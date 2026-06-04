<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Addresses;
use App\Models\Recipient;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

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

        $recipients = Recipient::with('addresses')
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

        return response()->json([
            'success' => true,
            'data' => $recipients->items(),
            'meta' => [
                'current_page' => $recipients->currentPage(),
                'last_page' => $recipients->lastPage(),
                'per_page' => $recipients->perPage(),
                'total' => $recipients->total(),
            ],
            'filters' => [
                'search' => $search,
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

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

        return response()->json([
            'success' => true,
            'message' => 'Recipient and addresses stored successfully!',
            'data' => $recipient->load('addresses')
        ], 201);
    }

    public function getAllWithAddresses(Request $request)
    {
        $user = $request->user();

        $recipients = Recipient::with('addresses')
            ->when($user->role === 'customer', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($recipient) {
                return [
                    'id' => $recipient->id,
                    'receiver_name' => $recipient->receiver_name,
                    'receiver_phone' => $recipient->receiver_phone,
                    'addresses' => $recipient->addresses->map(function ($address) {
                        return [
                            'id' => $address->id,
                            'address' => $address->address,
                            'city' => $address->city,
                            'state' => $address->state,
                            'country' => $address->country,
                            'zip_code' => $address->zip_code,
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $recipients
        ]);
    }
    public function edit($id, Request $request)
    {
        $user = $request->user();

        $recipient = Recipient::with('addresses')->findOrFail($id);

        if (
            $user->role === 'customer'
            && $recipient->user_id !== $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $recipient
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

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

        return response()->json([
            'success' => true,
            'message' => 'Recipient updated successfully',
            'data' => $recipient->load('addresses')
        ]);
    }
    public function getDetails($id)
    {
        $recipient = Recipient::with('addresses')->find($id);
        if ($recipient) {
            return response()->json([
                'success' => true,
                'data' => [
                    'phone' => $recipient->receiver_phone,
                    'addresses' => $recipient->addresses,
                ]
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Recipient not found'], 404);
    }


    public function destroy(Request $request, Recipient $recipient)
    {
        $user = $request->user();

        if ($user->role === 'customer' && $recipient->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $recipient->addresses()->delete();
        $recipient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recipient deleted successfully!'
        ]);
    }
}
