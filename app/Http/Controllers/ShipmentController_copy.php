<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Recipient;
use App\Models\Shipment;
use App\Models\User;
use App\Notifications\ShipmentAssignedNotification;
use App\Notifications\ShipmentCreatedNotification;
use App\Notifications\ShipmentStatusNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use App\Models\Payment;


class ShipmentController_copy extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        $users = User::select('name', 'id')->where('role', '=', 'agent')->get();

        $search = $request->search;
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';

        $allowedFields = [
            'tracking_id',
            'sender_name',
            'receiver_name',
            'status',
            'created_at'
        ];

        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'created_at';
        }

        $shipments = Shipment::query()

            ->when($search, function ($query) use ($search) {
                $query->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%");
            })

            ->when($user->role === 'customer', function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->when($user->role === 'agent', function ($query) use ($user) {
                $query->where('assigned_to', $user->id);
            })

            ->orderBy($sortField, $sortDirection)

            ->paginate(10)
            ->withQueryString();

        return view('pages.shipment.shipments', compact(
            'shipments',
            'search',
            'sortField',
            'sortDirection',
            'users'
        ));
    }


    public function adminDashboard()
    {
        $monthlyData = Shipment::select(
            DB::raw("DATE_FORMAT(shipments.created_at, '%Y-%m') as month"),
            DB::raw("COUNT(shipments.id) as total_shipments"),
            DB::raw("COALESCE(SUM(payments.amount), 0) as total_revenue")
        )
            ->leftJoin('payments', function ($join) {
                $join->on('shipments.id', '=', 'payments.shipment_id')
                    ->where('payments.payment_status', 'paid');
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $months = [];
        $shipments = [];
        $revenue = [];

        foreach ($monthlyData as $data) {
            $months[] = Carbon::parse($data->month . '-01')->format('M Y');

            $shipments[] = $data->total_shipments;

            $revenue[] = $data->total_revenue ?? 0;
        }



        $totalShipments = Shipment::count();

        $totalRevenue = Payment::where('payment_status', 'paid')->sum('amount');

        $totalUsers = User::count();

        $pendingPayments = Payment::where('payment_status', 'pending')->count();

        $ongoingShipments = Shipment::whereIn('status', ['pending_assigned', 'in_transit', 'out_for_delivery','picked_up'])->count();

        $completedShipments = Shipment::where('status', 'delivered')->count();

        $failedDeliveries = Shipment::where('status', 'failed')->count();


        $shipmentsByStatus = Shipment::select(
            'status',
            DB::raw("COUNT(*) as total")
        )
            ->groupBy('status')
            ->pluck('total', 'status');

        $delivered = Shipment::where('status', 'delivered')->count();
        $total = Shipment::count();

        $successRate = $total > 0 ? ($delivered / $total) * 100 : 0;

        $avgDeliveryTime = Shipment::whereNotNull('actual_delivery_date')
            ->select(DB::raw("AVG(DATEDIFF(actual_delivery_date, created_at)) as avg_days"))
            ->value('avg_days');

        $failed = Shipment::where('status', 'failed')->count();

        $failedRate = $total > 0 ? ($failed / $total) * 100 : 0;





        return view('pages.admindashboard', compact(
            'months',
            'shipments',
            'revenue',
            'totalShipments',
            'totalRevenue',
            'totalUsers',
            'pendingPayments',
            'ongoingShipments',
            'completedShipments',
            'failedDeliveries',
            'shipmentsByStatus',
            'successRate',
            'avgDeliveryTime',
            'failedRate'
        ));
    }



    public function paymentShipments(Request $request)
    {
        $user = auth()->user();

        $search = $request->search;
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';

        $allowedFields = [
            'tracking_id',
            'amount',
            'payment_method',
            'transaction_id',
            'payment_status',
            'paid_at',
        ];

        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'created_at';
        }

        $payments = Payment::query()

            ->when($search, function ($query) use ($search) {
                $query->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('payment_status', 'like', "%{$search}%");
            })

            ->when($user->role === 'customer', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })

            ->orderBy($sortField, $sortDirection)

            ->paginate(10)
            ->withQueryString();

        return view('pages.payment.paymentShipment', compact(
            'payments',
            'search',
            'sortField',
            'sortDirection',
        ));

    }
    public function getByID($shipmentId)
    {
        $shipment = Shipment::where('id', $shipmentId)
            ->with(['logs', 'images', 'invoice', 'packages', 'senderAddress', 'receiverAddress'])
            ->firstOrFail();

        return view('pages.shipment.getByIDShipment', compact('shipment'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:10',
            'sender_address_id' => 'required|exists:addresses,id',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:10',
            'package_type' => 'required|string',
            'receiver_address_id' => 'required|exists:addresses,id',
            'package_amount.*' => 'required|integer|min:1',
            'package_description.*' => 'required|string|max:255',
            'package_weight.*' => 'required|numeric|min:0.01',
            'package_length.*' => 'required|numeric|min:0.01',
            'package_width.*' => 'required|numeric|min:0.01',
            'package_height.*' => 'required|numeric|min:0.01',
            'package_notes.*' => 'nullable|string',
            'delivery_method' => 'required|in:standard,express',
            'package_photos' => 'required|array|min:1',
            'package_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'courier_company' => 'required|string',
            'shipping_mode' => 'required|string',
        ], [
            'package_amount.*.required' => 'Package quantity is required',
            'package_amount.*.min' => 'Quantity must be at least 1',
            'package_description.*.required' => 'Package description is required',
            'package_weight.*.required' => 'Package weight is required',
            'package_weight.*.numeric' => 'Weight must be numeric',
            'package_length.*.required' => 'Package length is required',
            'package_width.*.required' => 'Package width is required',
            'package_height.*.required' => 'Package height is required',
        ]);

        $recipient = Recipient::find($request->receiver_name);

        $shipment = Shipment::create([
            'tracking_id' => uniqid('TRK'),
            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'sender_address_id' => $request->sender_address_id,
            'package_type' => $request->package_type,
            'receiver_name' => $recipient->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'receiver_address_id' => $request->receiver_address_id,
            'notes' => $request->notes,
            'estimated_delivery_date' => $request->estimated_delivery_date,
            'delivery_method' => $request->delivery_method,
            'created_by' => auth()->id(),
            'status' => 'created',
            'courier_company' => $request->courier_company,
            'shipping_mode' => $request->shipping_mode,
        ]);

        $shipment->logs()->create([
            'status' => 'created',
            'location' => $shipment->senderAddress->address ?? 'N/A',
            'description' => 'Shipment created',
        ]);

        if ($request->hasFile('package_photos')) {
            foreach ($request->file('package_photos') as $photo) {
                $path = $photo->store('shipments', 'public');
                $shipment->images()->create([
                    'image_path' => $path,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $creator = User::find($shipment->created_by);

        if ($creator) {
            $creator->notify(new ShipmentStatusNotification($shipment));
        }

        $role = auth()->user()->role;


        $staffAdmins = User::whereIn('role', ['admin', 'staff'])->get();

        Notification::send(
            $staffAdmins,
            new ShipmentCreatedNotification($shipment)
        );

        $successRoute = $role . '.shipments.success';
        $cancelRoute = $role . '.shipments.cancel';


        $totalAmount = 0;
        $pricePerKg = 3;
        $taxPercent = 0.19;

        foreach ($request->package_weight as $i => $weight) {

            $length = $request->package_length[$i];
            $width = $request->package_width[$i];
            $height = $request->package_height[$i];
            $amount = $request->package_amount[$i];

            $volWeight = ($length * $width * $height) / 5000;

            $chargeableWeight = max($weight, $volWeight);

            $subtotal = $chargeableWeight * $pricePerKg;

            $totalAmount += $subtotal;

            $shipment->packages()->create([
                'amount' => $amount,
                'description' => $request->package_description[$i] ?? 'Package',
                'weight' => $weight,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'notes' => $request->package_notes[$i] ?? null,
            ]);
        }

        $insurance = 1;

        $tax = $totalAmount * $taxPercent;

        $totalAmount = $totalAmount + $insurance + $tax;

        if ($request->delivery_method == "express") {
            $totalAmount *= 1.5;
        }

        $totalAmount = max($totalAmount, 50);


        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => [
                            'name' => 'Shipment #' . $shipment->tracking_id,
                        ],
                        'unit_amount' => round($totalAmount * 100),
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route($successRoute, ['shipment' => $shipment->id]),
            'cancel_url' => route($cancelRoute, ['shipment' => $shipment->id]),
            'metadata' => [
                'shipment_id' => $shipment->id
            ]
        ]);

        return redirect($session->url);

    }

    public function edit(Shipment $shipment)
    {
        return view('pages.shipment.editShipment', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:10',
            'sender_address' => 'required|string',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:10',
            'receiver_address' => 'required|string',
            'package_type' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0.01',
            'length' => 'required|numeric|min:0.01',
            'width' => 'required|numeric|min:0.01',
            'height' => 'required|numeric|min:0.01',
            'notes' => 'required|string',
            'delivery_method' => 'required|in:standard,express',
        ]);

        $shipment->update([
            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'sender_address' => $request->sender_address,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'receiver_address' => $request->receiver_address,
            'package_type' => $request->package_type,
            'weight' => $request->weight,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'notes' => $request->notes,
            'delivery_method' => $request->delivery_method,
        ]);

        if ($request->hasFile('package_photos')) {
            $shipment->images()->delete();

            foreach ($request->file('package_photos') as $photo) {
                $path = $photo->store('shipments', 'public');

                $shipment->images()->create([
                    'image_path' => $path,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }
        $role = auth()->user()->role;

        return (match ($role) {
            'admin' => redirect()->route('admin.shipments'),
            'staff' => redirect()->route('staff.shipments'),
            'customer' => redirect()->route('customer.shipments'),
            default => redirect()->route('login'),
        })->with('success', 'Shipment updates successfully!');

    }

    public function generateInvoice(Shipment $shipment)
    {
        $invoice = $shipment->invoice;

        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not generated yet.');
        }

        return response()->file(storage_path('app/public/' . $invoice->pdf_path));
    }

    public function destroy(Shipment $shipment)
    {
        $user = auth()->user();


        foreach ($shipment->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $shipment->images()->delete();
        $shipment->logs()->delete();
        $shipment->invoice()->delete();

        $shipment->delete();

        $role = $user->role;
        return match ($role) {
            'admin' => redirect()->route('admin.shipments')->with('success', 'Shipment deleted successfully!'),
            'staff' => redirect()->route('staff.shipments')->with('success', 'Shipment deleted successfully!'),
            'customer' => redirect()->route('customer.shipments')->with('success', 'Shipment deleted successfully!'),
            default => redirect()->route('login'),
        };
    }
    public function success($shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);



        $shipment->status = 'pending_assigned';
        $shipment->save();

        $creator = User::find($shipment->created_by);

        if ($creator) {
            $creator->notify(new ShipmentStatusNotification($shipment));
        }

        $shipment->logs()->create([
            'status' => 'pending_assigned',
            'location' => $shipment->receiverAddress->receiver_address ?? 'N/A',
            'description' => 'Payment completed, shipment ready for assignment',
        ]);


        $role = auth()->user()->role;

        return (match ($role) {
            'admin' => redirect()->route('admin.shipments'),
            'staff' => redirect()->route('staff.shipments'),
            'customer' => redirect()->route('customer.shipments'),
            default => redirect()->route('login'),
        })->with('success', ' payment successfully');

    }

    public function cancel($shipmentId)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $shipment->status = "pending_payment";
        $shipment->save();
        $role = auth()->user()->role;

        $creator = User::find($shipment->created_by);

        if ($creator) {
            $creator->notify(new ShipmentStatusNotification($shipment));
        }
        $shipment->logs()->create([
            'status' => 'pending_payment',
            'location' => $shipment->senderAddress->receiver_address ?? 'N/A',
            'description' => 'Payment failed, shipment pending payment',
        ]);


        return (match ($role) {
            'admin' => redirect()->route('admin.shipments'),
            'staff' => redirect()->route('staff.shipments'),
            'customer' => redirect()->route('customer.shipments'),
            default => redirect()->route('login'),
        })->with('error', 'Shipment updated successfully, but payment failed');
    }




    public function payShipment(Shipment $shipment)
    {

        $role = auth()->user()->role;

        $successRoute = $role . '.shipments.success';
        $cancelRoute = $role . '.shipments.cancel';

        $totalAmount = 0;
        $pricePerKg = 3;
        $taxPercent = 0.19;
        $insurance = 1;

        foreach ($shipment->packages as $package) {
            $volWeight = ($package->length * $package->width * $package->height) / 5000;

            $chargeableWeight = max($package->weight, $volWeight);

            $totalAmount += $chargeableWeight * $pricePerKg;
        }

        $tax = $totalAmount * $taxPercent;
        $totalAmount = $totalAmount + $insurance + $tax;

        if ($shipment->delivery_method == "express") {
            $totalAmount *= 1.5;
        }

        $totalAmount = max($totalAmount, 50);
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => [
                            'name' => 'Shipment #' . $shipment->tracking_id,
                        ],
                        'unit_amount' => round($totalAmount * 100),
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route($successRoute, ['shipment' => $shipment->id]),
            'cancel_url' => route($cancelRoute, ['shipment' => $shipment->id]),
            'metadata' => [
                'shipment_id' => $shipment->id
            ]
        ]);

        return redirect($session->url);
    }
    public function showTrackForm()
    {
        return view('pages.shipment.trackShipment');
    }

    public function trackShipment(Request $request)
    {
        $request->validate([
            'tracking_id' => 'required|string|exists:shipments,tracking_id',
        ]);

        $shipment = Shipment::where('tracking_id', $request->tracking_id)
            ->with(['logs', 'images', 'invoice', 'packages', 'senderAddress', 'receiverAddress'])
            ->first();

        $role = auth()->user()->role;

        if (!$shipment) {
            return (match ($role) {
                'admin' => redirect()->route('admin.shipment.track.form'),
                'staff' => redirect()->route('staff.shipment.track.form'),
                'customer' => redirect()->route('customer.shipment.track.form'),
            })->with
                ('error', 'Shipment not found.');
        }



        return view('pages.shipment.trackShipment', compact('shipment'));
    }

    public function UpdateShipmentStatus(Request $request, Shipment $shipment)
    {
        $role = auth()->user()->role;

        $rules = [
            'status' => 'required',
            'delivery_proof' => 'required_if:status,delivered|image'
        ];

        if (in_array($role, ['staff', 'admin'])) {
            $rules['agentid'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $shipment->update([
            'status' => $request->status,
            'assigned_to' => $request->agentid ?? $shipment->assigned_to
        ]);

        $lastLog = $shipment->logs()->latest()->first();

        if (!$lastLog || $lastLog->status !== $request->status) {

            $shipment->logs()->create([
                'status' => $request->status,
                'location' => $shipment->sender_address,
                'description' => "Shipment is {$request->status}",
            ]);

        }
        if ($request->hasFile('delivery_proof')) {

            $path = $request->file('delivery_proof')->store('shipments', 'public');

            $shipment->images()->create([
                'image_path' => $path,
                'uploaded_by' => auth()->id(),
            ]);
        }

        $creator = User::find($shipment->created_by);

        if ($creator) {
            $creator->notify(new ShipmentStatusNotification($shipment));
        }


        if ($request->status === 'assigned') {

            $assignedStaff = User::find($shipment->assigned_to);
            if ($assignedStaff) {
                $assignedStaff->notify(new ShipmentAssignedNotification($shipment));
            }
        }
        session()->flash('success', 'Shipment status updated');


        if ($request->status === 'delivered' && !$shipment->invoice) {
            try {
                $payment = Payment::where('tracking_id', $shipment->tracking_id)->first();
                $totalAmount = $payment->amount;
                $pricePerKg = 3;
                $taxPercent = 0.19;
                $insurance = 1;


                $totalAmount = $payment->amount;

                $taxAmount = $totalAmount * $taxPercent;
                $shipment->actual_delivery_date = now();
                $shipment->save();

                $pdfContent = Pdf::loadView(
                    'pages.invoices.shipment_invoice',
                    [
                        'shipment' => $shipment,
                        'payment' => $payment,
                        'totalAmount' => $totalAmount,
                        'taxAmount' => $taxAmount,
                        'taxPercent' => $taxPercent,
                        'insurance' => $insurance
                    ]
                )->output();
                $filename = 'invoices/invoice_' . $shipment->tracking_id . '.pdf';
                Storage::disk('public')->put($filename, $pdfContent);

                Invoice::create([
                    'shipment_id' => $shipment->id,
                    'invoice_number' => 'INV-' . strtoupper(uniqid()),
                    'total_amount' => $totalAmount,
                    'pdf_path' => $filename,
                ]);

                Log::info("Invoice created for shipment {$shipment->tracking_id}");
            } catch (\Exception $e) {
                Log::error("Invoice generation failed: " . $e->getMessage());
            }
        }

        session()->flash('success', 'Shipment status updated');

        return response()->json([
            'success' => true,
            'redirect' => route(auth()->user()->role . '.shipments')
        ]);
    }

}
