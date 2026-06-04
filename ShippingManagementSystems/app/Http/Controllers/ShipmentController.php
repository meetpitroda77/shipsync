<?php

namespace App\Http\Controllers;

use App\Events\ShipmentStatusUpdated;
use App\Exports\ShippingReportExport;
use App\Http\Controllers\Controller;
use App\Models\Addresses;
use App\Models\Invoice;
use App\Models\Recipient;
use App\Models\Shipment;
use App\Models\User;
use App\Notifications\ShipmentAssignedNotification;
use App\Notifications\ShipmentCreatedNotification;
use App\Notifications\ShipmentStatusNotification;
use App\Services\SettingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use App\Models\Payment;
use Maatwebsite\Excel\Facades\Excel;
use Stripe\Customer as StripeCustomer;

class ShipmentController extends Controller
{


    public function index(Request $request)
    {
        $user = $request->user();
        $users = User::select('name', 'id')->where('role', '=', 'agent')->get();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $search = $request->search;
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $status = $request->status;
        $deliveryMethod = $request->delivery_method;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $perPage = $request->per_page ?? 10;

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
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_id', $search)
                        ->orWhere('sender_name', 'like', "{$search}%")
                        ->orWhere('receiver_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('created_at', 'like', "%{$search}%");
                });
            })
            ->when($user->role === 'customer', function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->when($user->role === 'agent', function ($query) use ($user) {
                $query->where('assigned_to', $user->id);
            })
            ->when($deliveryMethod, function ($query) use ($deliveryMethod) {
                $query->where('delivery_method', $deliveryMethod);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->when($startDate && !$endDate, fn($q) => $q->where('created_at', '>=', Carbon::parse($startDate)->startOfDay()))
            ->when(!$startDate && $endDate, fn($q) => $q->where('created_at', '<=', Carbon::parse($endDate)->endOfDay()))
            ->when($status, function ($query) use ($status) {
                if ($status === 'delivered_today') {
                    $query->whereDate('actual_delivery_date', today())
                        ->where('status', 'delivered');
                } elseif ($status === 'pending_delivery') {
                    $query->whereIn('status', [
                        'assigned',
                        'picked_up',
                        'in_transit',
                        'out_for_delivery',
                        'delayed',
                        'failed_delivery'
                    ]);
                } elseif ($status === 'ongoingShipments') {
                    $query->whereIn('status', [
                        'created',
                        'pending_assigned',
                        'pending_payment',
                        'assigned',
                        'picked_up',
                        'in_transit',
                        'out_for_delivery',
                        'delayed',
                        'failed_delivery'
                    ]);
                } else {
                    $query->where('status', $status);
                }
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $shipments->items(),
            'users' => $users,
            'user_role' => $user->role,
            'meta' => [
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'per_page' => $shipments->perPage(),
                'total' => $shipments->total(),
            ],
            'filters' => [
                'search' => $search,
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
                'status' => $status,
                'delivery_method' => $deliveryMethod,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }

    public function adminDashboard()
    {
        $monthlyData = Shipment::select(
            DB::raw("DATE_FORMAT(shipments.created_at, '%Y-%m') as month"),
            DB::raw("COUNT(shipments.id) as total_shipments"),
            DB::raw("SUM(payments.amount) as total_revenue")
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
        $completedShipments = Shipment::where('status', 'delivered')->count();
        $totalAttempted = Shipment::whereNotIn('status', ['canceled'])->count();
        $delivered = Shipment::where('status', 'delivered')->count();
        $deliverySuccessRate = $totalAttempted > 0 ? ($delivered / $totalAttempted) * 100 : 0;
        $avgDeliveryTime = Shipment::whereNotNull('actual_delivery_date')
            ->select(DB::raw("AVG(DATEDIFF(actual_delivery_date, created_at)) as avg_days"))
            ->value('avg_days');
        $ongoingShipments = Shipment::whereNull('actual_delivery_date')->whereIn('status', [
            'created',
            'pending_assigned',
            'pending_payment',
            'assigned',
            'picked_up',
            'in_transit',
            'out_for_delivery',
            'delayed',
            'failed_delivery'
        ])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'chart' => [
                    'months' => $months,
                    'shipments' => $shipments,
                    'revenue' => $revenue,
                ],
                'stats' => [
                    'total_shipments' => $totalShipments,
                    'total_revenue' => $totalRevenue,
                    'total_users' => $totalUsers,
                    'completed_shipments' => $completedShipments,
                    'delivery_success_rate' => round($deliverySuccessRate, 2),
                    'avg_delivery_time_days' => round($avgDeliveryTime ?? 0, 2),
                    'ongoing_shipments' => $ongoingShipments,
                ]
            ]
        ]);
    }

    public function customerDashboard(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $totalShipments = Shipment::where('created_by', $user->id)->count();
        $DeliveredShipments = Shipment::where('created_by', $user->id)->where('status', 'delivered')->count();
        $ongoingShipments = Shipment::where('created_by', $user->id)
            ->whereIn('status', [
                'created',
                'pending_assigned',
                'pending_payment',
                'assigned',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delayed',
                'failed_delivery'
            ])
            ->count();

        $totalSpent = Payment::where('user_id', $user->id)->where('payment_status', 'paid')->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_shipments' => $totalShipments,
                'delivered_shipments' => $DeliveredShipments,
                'ongoing_shipments' => $ongoingShipments,
                'total_spent' => $totalSpent,
            ]
        ]);
    }

    public function StaffDashboard()
    {
        $DeliveredToday = Shipment::where('status', 'delivered')->whereDate('actual_delivery_date', today())->count();
        $DelayedShipments = Shipment::where('status', 'delayed')->count();
        $PendingAssignments = Shipment::whereIn('status', ['pending_assigned', 'pending_payment'])->count();
        $failedDeliveries = Shipment::where('status', 'failed_delivery')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'delivered_today' => $DeliveredToday,
                'delayed_shipments' => $DelayedShipments,
                'pending_assignments' => $PendingAssignments,
                'failed_deliveries' => $failedDeliveries,
            ]
        ]);
    }

    public function AgentDashboard(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $assignedShipments = Shipment::where('assigned_to', $user->id)->count();
        $deliveredShipmentsToday = Shipment::where('assigned_to', $user->id)
            ->where('status', 'delivered')
            ->whereDate('actual_delivery_date', today())
            ->count();
        $pendingShipmentsDelivery = Shipment::where('assigned_to', $user->id)
            ->whereIn('status', [
                'assigned',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delayed',
                'failed_delivery'
            ])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'assigned_shipments' => $assignedShipments,
                'delivered_today' => $deliveredShipmentsToday,
                'pending_delivery' => $pendingShipmentsDelivery,
            ]
        ]);
    }

    public function paymentShipments(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $search = $request->search;
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $status = $request->status;
        $perPage = $request->per_page ?? 10;

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
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_id', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%")
                        ->orWhere('payment_status', 'like', "%{$search}%");
                });
            })
            ->when($user->role === 'customer', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('payment_status', $status);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                $query->whereBetween('paid_at', [$start, $end]);
            })
            ->when($startDate && !$endDate, fn($q) => $q->where('paid_at', '>=', Carbon::parse($startDate)->startOfDay()))
            ->when(!$startDate && $endDate, fn($q) => $q->where('paid_at', '<=', Carbon::parse($endDate)->endOfDay()))
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
            'filters' => [
                'search' => $search,
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }

    public function getByID($shipmentId)
    {
        $shipment = Shipment::where('id', $shipmentId)
            ->with(['logs', 'images', 'invoice', 'packages', 'senderAddress', 'receiverAddress'])
            ->firstOrFail();

        if ($shipment->images) {
            foreach ($shipment->images as &$image) {
                $cleanPath = preg_replace('/^storage\//', '', $image['image_path']);
                $image['full_url'] = url('/storage/' . $cleanPath);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $shipment
        ]);
    }

    public function validateShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:10',
            'sender_address_id' => 'required|exists:addresses,id',
            'receiver_name' => 'required|exists:recipients,id',
            'receiver_phone' => 'required|string|max:10',
            'package_type' => 'required|string',
            'receiver_address_id' => 'required|exists:addresses,id',
            'package_amount' => 'required|array|min:1',
            'package_amount.*' => 'required|integer|min:1',
            'package_description' => 'required|array|min:1',
            'package_description.*' => 'required|string|max:255',
            'package_weight' => 'required|array|min:1',
            'package_weight.*' => 'required|numeric|min:0.01',
            'package_length' => 'required|array|min:1',
            'package_length.*' => 'required|numeric|min:0.01',
            'package_width' => 'required|array|min:1',
            'package_width.*' => 'required|numeric|min:0.01',
            'package_height' => 'required|array|min:1',
            'package_height.*' => 'required|numeric|min:0.01',
            'package_notes' => 'nullable|array',
            'package_notes.*' => 'nullable|string',
            'delivery_method' => 'required|in:standard,express',
            'package_photos' => 'required|array|min:1',
            'package_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'courier_company' => 'required|string',
            'shipping_mode' => 'required|string',
        ], [
            'package_amount.required' => 'Package quantity is required',
            'package_amount.min' => 'At least one package is required',
            'package_description.required' => 'Package description is required',
            'package_weight.required' => 'Package weight is required',
            'package_length.required' => 'Package length is required',
            'package_width.required' => 'Package width is required',
            'package_height.required' => 'Package height is required',
            'package_amount.*.required' => 'Package quantity is required',
            'package_amount.*.min' => 'Quantity must be at least 1',
            'package_description.*.required' => 'Package description is required',
            'package_weight.*.required' => 'Package weight is required',
            'package_weight.*.numeric' => 'Weight must be numeric',
            'package_length.*.required' => 'Package length is required',
            'package_width.*.required' => 'Package width is required',
            'package_height.*.required' => 'Package height is required',
        ]);

        if ($validator->fails()) {
            $errors = collect($validator->errors())->map(function ($messages, $key) {
                return [
                    'field' => str_replace('.', '_', $key),
                    'message' => $messages[0]
                ];
            })->values();

            return response()->json([
                'success' => false,
                'errors' => $errors
            ], 422);
        }

        $sender = Addresses::find($request->sender_address_id);
        $receiver = Addresses::find($request->receiver_address_id);
        $receiverofname = Recipient::find($request->receiver_name);
        $packages = [];
        $subtotal = 0;

        foreach ($request->package_weight as $i => $w) {
            $l = $request->package_length[$i];
            $wd = $request->package_width[$i];
            $h = $request->package_height[$i];
            $qty = $request->package_amount[$i];

            $vol = ($l * $wd * $h) / 5000;
            $charge = max($w, $vol);
            $pricePerKg = SettingService::get('price_per_kg');

            $cost = $charge * $pricePerKg * $qty;
            $subtotal += $cost;
            $taxPercent = SettingService::get('tax_percent');

            $packages[] = [
                'qty' => $qty,
                'weight' => $w,
                'dimensions' => "$l x $wd x $h",
            ];
        }

        $tax = $subtotal * $taxPercent;
        $total = $subtotal + $tax + 1;

        if ($request->delivery_method == 'express')
            $total *= 1.5;

        return response()->json([
            'success' => true,
            'preview' => [
                'sender_name' => $request->sender_name,
                'sender_phone' => $request->sender_phone,
                'sender_address' => $sender?->address . ', ' .
                    $sender?->city . ', ' .
                    $sender?->state,
                'receiver_name' => $receiverofname->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'receiver_address' => $receiver?->address . ', ' .
                    $receiver?->city . ', ' .
                    $receiver?->state,
                'packages' => $packages,
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'total' => round($total, 2),
                'delivery_method' => $request->delivery_method,
                'delivery_date' => $request->estimated_delivery_date
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            if (!$user->canCreateShipment()) {
                return response()->json([
                    'success' => false,
                    'message' => $user->getCannotCreateShipmentReason()
                ], 403);
            }


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
                'created_by' => $user->id,
                'status' => 'created',
                'courier_company' => $request->courier_company,
                'shipping_mode' => $request->shipping_mode,
            ]);

            $user->incrementShipmentUsage();

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
                        'uploaded_by' => $user->id,
                    ]);
                }
            }

            $creator = User::find($shipment->created_by);
            if ($creator) {
                $creator->notify(new ShipmentStatusNotification($shipment, 'created'));
            }

            $staffAdmins = User::whereIn('role', ['admin', 'staff'])->get();
            Notification::send($staffAdmins, new ShipmentCreatedNotification($shipment));

            $totalAmount = 0;
            $pricePerKg = SettingService::get('price_per_kg');
            $taxPercent = SettingService::get('tax_percent');

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

            $insurance = SettingService::get('insurance');
            $tax = $totalAmount * $taxPercent;
            $finalTotal = $totalAmount + $insurance + $tax;

            if ($request->delivery_method == "express") {
                $finalTotal *= 1.5;
            }

            $finalTotal = max($finalTotal, 50);

            $shipment->update(['status' => 'pending_payment']);
            $shipment->logs()->create([
                'status' => 'pending_payment',
                'location' => $shipment->senderAddress->address ?? 'N/A',
                'description' => 'Waiting for payment'
            ]);
            if (!$user->stripe_id) {
                $user->createAsStripeCustomer();
            }
            if (!$user->stripe_id) {
                $customer = StripeCustomer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'metadata' => ['user_id' => $user->id],
                ]);
                $user->stripe_id = $customer->id;
                $user->save();
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Shipment #' . $shipment->tracking_id,
                            ],
                            'unit_amount' => round($finalTotal * 100),
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'client_reference_id' => $shipment->id,
                'payment_intent_data' => [
                    'metadata' => [
                        'shipment_id' => $shipment->id
                    ]
                ],
                'success_url' => route('payment.success', ['shipmentId' => $shipment->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', ['shipmentId' => $shipment->id]),
            ]);

            Payment::create([
                'shipment_id' => $shipment->id,
                'tracking_id' => $shipment->tracking_id,
                'user_id' => $shipment->created_by,
                'session_id' => $session->id,
                'amount' => $finalTotal,
                'subtotal' => $totalAmount,
                'tax' => $tax,
                'insurance' => $insurance,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shipment created successfully',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'tracking_id' => $shipment->tracking_id,
                    'payment_url' => $session->url,
                    'payment_session_id' => $session->id
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Shipment $shipment)
    {
        $allowedFields = ['notes', 'estimated_delivery_date'];
        $data = $request->only($allowedFields);

        $shipment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Shipment updated successfully',
            'data' => $shipment
        ]);
    }

    public function destroy(Request $request, Shipment $shipment)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        foreach ($shipment->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $shipment->images()->delete();
        $shipment->logs()->delete();
        $shipment->invoice()->delete();
        $shipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipment deleted successfully'
        ]);
    }

    public function generateInvoice(Shipment $shipment)
    {
        $invoice = $shipment->invoice;

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not generated yet.'
            ], 404);
        }

        $filePath = storage_path('app/public/' . $invoice->pdf_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice file not found.'
            ], 404);
        }

        return response()->download($filePath, 'invoice_' . $shipment->tracking_id . '.pdf');
    }

    public function success($shipmentId, Request $request)
    {
        $sessionId = $request->query('session_id');

        $payment = Payment::where('session_id', $sessionId)
            ->where('shipment_id', $shipmentId)
            ->first();

        if (!$payment) {
            abort(404, 'Payment not found');
        }

        $shipment = Shipment::find($shipmentId);

        return redirect()->to(config('app.frontend_url') . "/payment/success?shipment_id={$shipmentId}&session_id={$sessionId}");
    }

    public function cancel($shipmentId)
    {
        $shipment = Shipment::find($shipmentId);

        if ($shipment) {
            $payment = Payment::where('shipment_id', $shipmentId)
                ->where('payment_status', 'pending')
                ->latest()
                ->first();

            if ($payment) {
                $payment->update([
                    'paid_at' => null,
                ]);
            }

            if ($shipment->status === 'pending_payment') {
                $shipment->logs()->create([
                    'status' => 'payment_cancelled',
                    'description' => 'Payment was cancelled by user',
                    'location' => $shipment->senderAddress->address ?? 'N/A'
                ]);
            }
        }

        return redirect()->to(config('app.frontend_url') . "/payment/cancel?shipment_id={$shipmentId}");
    }

    public function getPaymentStatus($shipmentId, Request $request)
    {
        $sessionId = $request->query('session_id');

        $payment = Payment::where('shipment_id', $shipmentId)
            ->where('session_id', $sessionId)
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $shipment = Shipment::find($shipmentId);

        return response()->json([
            'status' => $payment->payment_status,
            'amount' => $payment->amount,
            'tracking_id' => $payment->tracking_id,
            'shipment' => [
                'tracking_id' => $shipment->tracking_id,
                'amount' => $payment->amount,
                'status' => $shipment->status
            ]
        ]);
    }

    public function payShipment(Request $request, Shipment $shipment)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $payment = $shipment->payments()->latest()->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $totalAmount = $payment->amount;
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Shipment #' . $shipment->tracking_id,
                        ],
                        'unit_amount' => round($totalAmount * 100),
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'customer_email' => $user->email,
            'payment_intent_data' => [
                'metadata' => [
                    'shipment_id' => $shipment->id
                ]
            ],
            'success_url' => route('api.payment.success.callback') . '?session_id={CHECKOUT_SESSION_ID}&shipment_id=' . $shipment->id,
            'cancel_url' => route('api.payment.cancel.callback') . '?shipment_id=' . $shipment->id,
        ]);

        $payment->update([
            'session_id' => $session->id
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_url' => $session->url,
                'payment_session_id' => $session->id
            ]
        ]);
    }

    public function trackShipmentApi(Request $request)
    {
        $tracking = $request->tracking_id;


        $validator = Validator::make(
            ['tracking_number' => $tracking],
            ['tracking_number' => 'required|string|exists:shipments,tracking_id']
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $shipment = Shipment::where('tracking_id', $tracking)
            ->with(['logs', 'images', 'invoice', 'packages', 'senderAddress', 'receiverAddress'])
            ->first();

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment
        ]);
    }

    public function UpdateShipmentStatus(Request $request, Shipment $shipment)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $role = $user->role;

        $rules = [
            'status' => 'required|string',
            'delivery_proof' => 'required_if:status,delivered|image|max:2048',
            'failed_reason' => 'required_if:status,failed_delivery|string',
        ];

        if (in_array($role, ['staff', 'admin'])) {
            $rules['agentid'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = collect($validator->errors())->map(function ($messages, $key) {
                $field = preg_replace('/\.\d+/', '', $key);
                return [
                    'field' => $field,
                    'message' => $messages[0]
                ];
            })->values();

            return response()->json([
                'success' => false,
                'errors' => $errors
            ], 422);
        }

        $shipment->update([
            'status' => $request->status,
            'assigned_to' => $request->agentid ?? $shipment->assigned_to
        ]);

        broadcast(new ShipmentStatusUpdated($shipment))->toOthers();

        $existingLog = $shipment->logs()
            ->where('status', $request->status)
            ->first();

        if ($existingLog) {
            $existingLog->update([
                'location' => $shipment->sender_address,
                'description' => $request->status === 'failed_delivery'
                    ? $request->failed_reason
                    : $existingLog->description,
            ]);
        } else {
            $shipment->logs()->create([
                'status' => $request->status,
                'location' => $shipment->sender_address,
                'description' => $request->status === 'failed_delivery'
                    ? $request->failed_reason
                    : ucfirst(str_replace('_', ' ', $request->status)),
            ]);
        }

        if ($request->hasFile('delivery_proof')) {
            $path = $request->file('delivery_proof')->store('shipments', 'public');
            $shipment->images()->create([
                'image_path' => $path,
                'uploaded_by' => $user->id,
            ]);
        }

        $creator = User::find($shipment->created_by);
        if ($creator) {
            $creator->notify(new ShipmentStatusNotification($shipment));
        }

        if ($request->status === 'assigned' && $request->agentid) {
            $assignedStaff = User::find($request->agentid);
            if ($assignedStaff) {
                $assignedStaff->notify(new ShipmentAssignedNotification($shipment));
            }
        }

        if ($request->status === 'delivered' && !$shipment->invoice) {
            try {
                // Log that we're attempting invoice creation
                Log::info("Attempting invoice creation for shipment: " . $shipment->tracking_id);

                $payment = Payment::where('tracking_id', $shipment->tracking_id)->first();

                if (!$payment) {
                    Log::warning("No payment found for tracking_id: " . $shipment->tracking_id);
                } else {
                    $totalAmount = $payment->amount;
                    $taxPercent = SettingService::get('tax_percent');
                    $insurance = SettingService::get('insurance');

                    $amountWithoutInsurance = $totalAmount - $insurance;
                    $subtotal = $amountWithoutInsurance / (1 + $taxPercent);
                    $taxAmount = $subtotal * $taxPercent;

                    $calculatedTotal = $subtotal + $taxAmount + $insurance;
                    $shipment->actual_delivery_date = now();
                    $shipment->save();

                    Log::info("Invoice breakdown - Subtotal: $subtotal, Tax: $taxAmount, Insurance: $insurance, Total: $calculatedTotal");

                    $pdfContent = Pdf::loadView(
                        'pages.invoices.shipment_invoice',
                        [
                            'shipment' => $shipment,
                            'payment' => $payment,
                            'totalAmount' => $totalAmount,
                            'subtotal' => $subtotal,
                            'taxAmount' => $taxAmount,
                            'taxPercent' => $taxPercent,
                            'insurance' => $insurance
                        ]
                    )->output();

                    $filename = 'invoices/invoice_' . $shipment->tracking_id . '.pdf';
                    Storage::disk('public')->put($filename, $pdfContent);

                    $invoice = Invoice::create([
                        'shipment_id' => $shipment->id,
                        'invoice_number' => 'INV-' . strtoupper(uniqid()),
                        'total_amount' => $totalAmount,
                        'pdf_path' => $filename,
                    ]);

                    Log::info("Invoice created successfully. ID: " . $invoice->id);
                }
            } catch (\Exception $e) {
                Log::error("Invoice generation failed: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'shipment_id' => $shipment->id,
                    'tracking_id' => $shipment->tracking_id
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Shipment status updated successfully',
            'data' => [
                'shipment_id' => $shipment->id,
                'status' => $shipment->status
            ]
        ]);
    }

    public function report(Request $request)
    {
        $data = $this->getReportData($request, true);

        return response()->json([
            'success' => true,
            'data' => [
                'report' => $data['report'],
                'totals' => $data['totals'],
                'filters' => [
                    'start_date' => $data['startDate'],
                    'end_date' => $data['endDate'],
                    'status' => $data['status'],
                    'delivery_method' => $data['deliveryMethod'],
                ],
                'pagination' => [
                    'current_page' => $data['shipments']->currentPage(),
                    'last_page' => $data['shipments']->lastPage(),
                    'per_page' => $data['shipments']->perPage(),
                    'total' => $data['shipments']->total(),
                ]
            ]
        ]);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $pdf = Pdf::loadView('pages.reports.shippingPdf', $data)
            ->setPaper('A4', 'landscape');

        $pdfContent = $pdf->output();
        $filename = 'shipping-report-' . now()->format('Y-m-d-His') . '.pdf';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getReportData($request);

        return Excel::download(
            new ShippingReportExport($data),
            'shipping-report-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getReportData($request);

        return Excel::download(
            new ShippingReportExport($data),
            'shipping-report-' . now()->format('Y-m-d-His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }


    private function getReportData($request, $paginate = false)
    {
        $query = Shipment::with(['packages', 'senderAddress', 'payments']);

        $startDate = $request->from;
        $endDate = $request->to;
        $status = $request->status;
        $deliveryMethod = $request->delivery_method;

        if ($startDate && !$endDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate && !$startDate) {
            $query->where('created_at', '<=', $endDate);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($deliveryMethod) {
            $query->where('delivery_method', $deliveryMethod);
        }

        $shipments = $paginate
            ? $query->paginate(10)->withQueryString()
            : $query->get();

        $collection = $paginate ? $shipments->getCollection() : $shipments;

        $report = $collection->map(function ($shipment) {
            $weight = $shipment->packages->sum('weight');
            $payment = $shipment->payments
                ->where('payment_status', 'paid')
                ->first();

            return [
                'tracking' => $shipment->tracking_id,
                'date' => $shipment->created_at->format('Y-m-d'),
                'sender' => $shipment->sender_name,
                'origin' => optional($shipment->senderAddress)->city . ', ' .
                    optional($shipment->senderAddress)->country,
                'status' => $shipment->status,
                'weight' => $weight,
                'subtotal' => $payment ? round($payment->subtotal, 2) : 0,
                'insurance' => $payment ? round($payment->insurance, 2) : 0,
                'tax' => $payment ? round($payment->tax, 2) : 0,
                'total' => $payment ? round($payment->amount, 2) : 0,
            ];
        })->filter();

        if ($paginate) {
            $shipments->setCollection($report);
        }

        $totals = [
            'weight' => $report->sum('weight'),
            'subtotal' => $report->sum('subtotal'),
            'insurance' => $report->sum('insurance'),
            'tax' => $report->sum('tax'),
            'total' => $report->sum('total'),
        ];

        return compact('report', 'totals', 'startDate', 'endDate', 'status', 'deliveryMethod', 'shipments');
    }
}
   // public function success($shipmentId)
    // {
    //     // $shipment = Shipment::findOrFail($shipmentId);



    //     // $shipment->status = 'pending_assigned';
    //     // $shipment->save();

    //     // $creator = User::find($shipment->created_by);

    //     // if ($creator) {
    //     //     $creator->notify(new ShipmentStatusNotification($shipment));
    //     // }

    //     // $shipment->logs()->create([
    //     //     'status' => 'pending_assigned',
    //     //     'location' => $shipment->receiverAddress->receiver_address ?? 'N/A',
    //     //     'description' => 'Payment completed, shipment ready for assignment',
    //     // ]);


    //     $role = auth()->user()->role;

    //     return redirect()
    //         ->route($role . '.shipments')
    //         ->with('success', 'payment successfully');
    // }



//  public function update(Request $request, Shipment $shipment)
//     {
//         $request->validate([
//             'sender_name' => 'required|string|max:255',
//             'sender_phone' => 'required|string|max:10',
//             'sender_address' => 'required|string',
//             'receiver_name' => 'required|string|max:255',
//             'receiver_phone' => 'required|string|max:10',
//             'receiver_address' => 'required|string',
//             'package_type' => 'required|string|max:255',
//             'weight' => 'required|numeric|min:0.01',
//             'length' => 'required|numeric|min:0.01',
//             'width' => 'required|numeric|min:0.01',
//             'height' => 'required|numeric|min:0.01',
//             'notes' => 'required|string',
//             'delivery_method' => 'required|in:standard,express',
//         ]);

//         $shipment->update([
//             'sender_name' => $request->sender_name,
//             'sender_phone' => $request->sender_phone,
//             'sender_address' => $request->sender_address,
//             'receiver_name' => $request->receiver_name,
//             'receiver_phone' => $request->receiver_phone,
//             'receiver_address' => $request->receiver_address,
//             'package_type' => $request->package_type,
//             'weight' => $request->weight,
//             'length' => $request->length,
//             'width' => $request->width,
//             'height' => $request->height,
//             'notes' => $request->notes,
//             'delivery_method' => $request->delivery_method,
//         ]);

//         if ($request->hasFile('package_photos')) {
//             $shipment->images()->delete();

//             foreach ($request->file('package_photos') as $photo) {
//                 $path = $photo->store('shipments', 'public');

//                 $shipment->images()->create([
//                     'image_path' => $path,
//                     'uploaded_by' => auth()->id(),
//                 ]);
//             }
//         }
//         $role = auth()->user()->role;

//         return (match ($role) {
//             'admin' => redirect()->route('admin.shipments'),
//             'staff' => redirect()->route('staff.shipments'),
//             'customer' => redirect()->route('customer.shipments'),
//             default => redirect()->route('login'),
//         })->with('success', 'Shipment updates successfully!');
//     }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'sender_name' => 'required|string|max:255',
    //         'sender_phone' => 'required|string|max:10',
    //         'sender_address_id' => 'required|exists:addresses,id',
    //         'receiver_name' => 'required|string|max:255',
    //         'receiver_phone' => 'required|string|max:10',
    //         'package_type' => 'required|string',
    //         'receiver_address_id' => 'required|exists:addresses,id',
    //         'package_amount.*' => 'required|integer|min:1',
    //         'package_description.*' => 'required|string|max:255',
    //         'package_weight.*' => 'required|numeric|min:0.01',
    //         'package_length.*' => 'required|numeric|min:0.01',
    //         'package_width.*' => 'required|numeric|min:0.01',
    //         'package_height.*' => 'required|numeric|min:0.01',
    //         'package_notes.*' => 'nullable|string',
    //         'delivery_method' => 'required|in:standard,express',
    //         'package_photos' => 'required|array|min:1',
    //         'package_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'courier_company' => 'required|string',
    //         'shipping_mode' => 'required|string',
    //     ], [
    //         'package_amount.*.required' => 'Package quantity is required',
    //         'package_amount.*.min' => 'Quantity must be at least 1',
    //         'package_description.*.required' => 'Package description is required',
    //         'package_weight.*.required' => 'Package weight is required',
    //         'package_weight.*.numeric' => 'Weight must be numeric',
    //         'package_length.*.required' => 'Package length is required',
    //         'package_width.*.required' => 'Package width is required',
    //         'package_height.*.required' => 'Package height is required',
    //     ]);

    //     $recipient = Recipient::find($request->receiver_name);

    //     $shipment = Shipment::create([
    //         'tracking_id' => uniqid('TRK'),
    //         'sender_name' => $request->sender_name,
    //         'sender_phone' => $request->sender_phone,
    //         'sender_address_id' => $request->sender_address_id,
    //         'package_type' => $request->package_type,
    //         'receiver_name' => $recipient->receiver_name,
    //         'receiver_phone' => $request->receiver_phone,
    //         'receiver_address_id' => $request->receiver_address_id,
    //         'notes' => $request->notes,
    //         'estimated_delivery_date' => $request->estimated_delivery_date,
    //         'delivery_method' => $request->delivery_method,
    //         'created_by' => auth()->id(),
    //         'status' => 'created',
    //         'courier_company' => $request->courier_company,
    //         'shipping_mode' => $request->shipping_mode,
    //     ]);

    //     $shipment->logs()->create([
    //         'status' => 'created',
    //         'location' => $shipment->senderAddress->address ?? 'N/A',
    //         'description' => 'Shipment created',
    //     ]);

    //     if ($request->hasFile('package_photos')) {
    //         foreach ($request->file('package_photos') as $photo) {
    //             $path = $photo->store('shipments', 'public');
    //             $shipment->images()->create([
    //                 'image_path' => $path,
    //                 'uploaded_by' => auth()->id(),
    //             ]);
    //         }
    //     }

    //     $creator = User::find($shipment->created_by);

    //     if ($creator) {


    //         $creator->notify(
    //             new ShipmentStatusNotification($shipment, 'created')
    //         );
    //     }
    //     $role = auth()->user()->role;


    //     $staffAdmins = User::whereIn('role', ['admin', 'staff'])->get();

    //     Notification::send(
    //         $staffAdmins,
    //         new ShipmentCreatedNotification($shipment)
    //     );

    //     $successRoute = $role . '.shipments.success';
    //     $cancelRoute = $role . '.shipments.cancel';


    //     $totalAmount = 0;
    //     $pricePerKg = 3;
    //     $taxPercent = 0.19;

    //     foreach ($request->package_weight as $i => $weight) {

    //         $length = $request->package_length[$i];
    //         $width = $request->package_width[$i];
    //         $height = $request->package_height[$i];
    //         $amount = $request->package_amount[$i];

    //         $volWeight = ($length * $width * $height) / 5000;

    //         $chargeableWeight = max($weight, $volWeight);

    //         $subtotal = $chargeableWeight * $pricePerKg;

    //         $totalAmount += $subtotal;

    //         $shipment->packages()->create([
    //             'amount' => $amount,
    //             'description' => $request->package_description[$i] ?? 'Package',
    //             'weight' => $weight,
    //             'length' => $length,
    //             'width' => $width,
    //             'height' => $height,
    //             'notes' => $request->package_notes[$i] ?? null,
    //         ]);
    //     }

    //     $insurance = 1;

    //     $tax = $totalAmount * $taxPercent;

    //     $totalAmount = $totalAmount + $insurance + $tax;

    //     if ($request->delivery_method == "express") {
    //         $totalAmount *= 1.5;
    //     }

    //     $totalAmount = max($totalAmount, 50);


    //     Stripe::setApiKey(env('STRIPE_SECRET'));

    //     $session = CheckoutSession::create([
    //         'payment_method_types' => ['card'],
    //         'line_items' => [
    //             [
    //                 'price_data' => [
    //                     'currency' => 'usd',
    //                     'product_data' => [
    //                         'name' => 'Shipment #' . $shipment->tracking_id,
    //                     ],
    //                     'unit_amount' => round($totalAmount * 100),
    //                 ],
    //                 'quantity' => 1,
    //             ]
    //         ],
    //         'mode' => 'payment',
    //         'success_url' => route($successRoute, ['shipment' => $shipment->id]),
    //         'cancel_url' => route($cancelRoute, ['shipment' => $shipment->id]),
    //         'metadata' => [
    //             'shipment_id' => $shipment->id
    //         ]
    //     ]);

    //     if ($shipment->status == 'created') {

    //         $shipment->status = 'pending_payment';
    //         $shipment->save();

    //         $shipment->logs()->create([
    //             'status' => 'pending_payment',
    //             'location' => $shipment->senderAddress->address ?? 'N/A',
    //             'description' => 'Waiting for payment'
    //         ]);
    //     }


    //     return redirect($session->url);
    // }





    // public function success(Request $request, $shipmentId)
    // {
    //     $shipment = Shipment::find($shipmentId);

    //     if (!$shipment) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Shipment not found'
    //         ], 404);
    //     }

    //     $payment = Payment::where('shipment_id', $shipment->id)->first();

    //     if (!$payment) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Payment record not found'
    //         ], 404);
    //     }

    //     if ($request->session_id) {
    //         Stripe::setApiKey(config('services.stripe.secret'));

    //         $session = StripeSession::retrieve($request->session_id);

    //         if ($session && $session->payment_status === 'paid') {

    //             if ($payment->payment_status !== 'paid') {
    //                 $payment->update([
    //                     'transaction_id' => $session->payment_intent,
    //                     'payment_status' => 'paid',
    //                     'paid_at' => now(),
    //                 ]);

    //                 $shipment->update([
    //                     'status' => 'pending_assigned'
    //                 ]);
    //             }

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Payment successful',
    //                 'data' => [
    //                     'shipment_id' => $shipment->id,
    //                     'tracking_id' => $shipment->tracking_id,
    //                     'payment_status' => 'paid'
    //                 ]
    //             ]);
    //         }
    //     }

    //     if ($payment->payment_status === 'paid') {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Payment already completed',
    //             'data' => [
    //                 'shipment_id' => $shipment->id,
    //                 'tracking_id' => $shipment->tracking_id,
    //                 'payment_status' => 'paid'
    //             ]
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Payment not completed'
    //     ], 400);
    // }
    // public function cancel($shipmentId)
    // {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Payment is cancelled'
    //     ]);
    // }
