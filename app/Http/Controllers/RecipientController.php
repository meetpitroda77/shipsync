<?php

namespace App\Http\Controllers;

use App\Models\Addresses;
use App\Models\Recipient;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $search = $request->search;
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';

        $allowedFields = [
            'id',
            'receiver_name',
            'receiver_phone',
        ];

        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'created_at';
        }

        $recipients = Recipient::query()

            ->when($search, function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%")
                    ->orWhere('receiver_phone', 'like', "%{$search}%");
            })
            ->when($user->role === 'customer', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        return view('pages.recipient.index', compact(
            'recipients',
            'search',
            'sortField',
            'sortDirection'
        ));
    }



    public function recipientform()
    {
        return view('pages.recipient.create');
    }
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'addresses' => 'required|array|min:1',
            'addresses.*.address' => 'required|string|max:255',
            'addresses.*.city' => 'required|string|max:100',
            'addresses.*.state' => 'required|string|max:100',
            'addresses.*.country' => 'required|string|max:100',
            'addresses.*.zip_code' => 'required|string|max:20',
        ]);

        $recipient = Recipient::create([
            'receiver_name' => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'user_id' => $user->id,
        ]);

        foreach ($validated['addresses'] as $addressData) {
            Addresses::create(array_merge(
                $addressData,
                [
                    'recipient_id' => $recipient->id,
                    'user_id' => $user->id
                ]
            ));
        }


        $role = auth()->user()->role;

        return (match ($role) {
            'admin' => redirect()->route('admin.recipient.index'),
            'customer' => redirect()->route('customer.recipient.index'),
            default => redirect()->route('login'),
        })
            ->with('success', 'Recipient and addresses stored successfully!');
    }



    public function edit($id)
    {
        $recipient = Recipient::with('addresses')->findOrFail($id);

        if (
            auth()->user()->role === 'customer'
            && $recipient->user_id !== auth()->id()
        ) {
            abort(403);
        }

        return view(
            'pages.recipient.edit',
            compact('recipient')
        );
    }
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $validated = $request->validate([

            'receiver_name' => 'required|string|max:255',

            'receiver_phone' => 'required|string|max:20',

            'addresses' => 'required|array|min:1',
            'addresses.*.id' => 'nullable|exists:addresses,id',

            'addresses.*.address' => 'required|string|max:255',

            'addresses.*.city' => 'required|string|max:100',

            'addresses.*.state' => 'required|string|max:100',

            'addresses.*.country' => 'required|string|max:100',

            'addresses.*.zip_code' => 'required|string|max:20',

        ]);

        $recipient = Recipient::findOrFail($id);

        $recipient->update([

            'receiver_name' => $validated['receiver_name'],

            'receiver_phone' => $validated['receiver_phone']

        ]);

        $submittedIds = collect($validated['addresses'])
            ->pluck('id')
            ->filter()
            ->toArray();

        $recipient->addresses()
            ->whereNotIn('id', $submittedIds)
            ->delete();

        foreach ($validated['addresses'] as $addressData) {

            if (!empty($addressData['id'])) {
                $address = $recipient->addresses()
                    ->find($addressData['id']);

                if ($address) {
                    $address->update([
                        'address' => $addressData['address'],
                        'city' => $addressData['city'],
                        'state' => $addressData['state'],
                        'country' => $addressData['country'],
                        'zip_code' => $addressData['zip_code'],
                    ]);

                    continue;
                }
            }

            $recipient->addresses()->create([
                'address' => $addressData['address'],
                'city' => $addressData['city'],
                'state' => $addressData['state'],
                'country' => $addressData['country'],
                'zip_code' => $addressData['zip_code'],
                'recipient_id' => $recipient->id,
                'user_id' => $user->id
            ]);
        }


        return redirect()->route("{$user->role}.recipient.index")
            ->with('success', 'Recipient updated successfully');

    }

    public function destroy(Recipient $recipient)
    {
        if (auth()->user()->role === 'customer' && $recipient->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $recipient->addresses()->delete();

        $recipient->delete();

        $role = auth()->user()->role;

        return (match ($role) {
            'admin' => redirect()->route('admin.recipient.index'),
            'customer' => redirect()->route('customer.recipient.index'),
            default => redirect()->route('login'),
        })
            ->with('success', 'Recipient deleted successfully!');
    }

}

