<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendVerificationEmailJob;
use App\Models\User;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $search = $request->search;
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $roleuser = $request->role;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $allowedFields = [
            'id',
            'name',
            'email',
            'role',
            'email_verified_at',
            'created_at',
            'updated_at'
        ];

        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'created_at';
        }

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            })
            ->when($roleuser, function ($query) use ($roleuser) {
                $query->where('role', $roleuser);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when($startDate && !$endDate, function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->when(!$startDate && $endDate, function ($query) use ($endDate) {
                $query->where('created_at', '<=', $endDate);
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'filters' => [
                'search' => $search,
                'sort_field' => $sortField,
                'sort_direction' => $sortDirection,
            ]
        ]);
    }

    public function destroy(User $user)
    {
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        foreach ($user->shipmentsCreated as $shipment) {
            foreach ($shipment->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }


            $shipment->images()->delete();
            $shipment->logs()->delete();
            $shipment->packages()->delete();
            $shipment->payments()->delete();

            if ($shipment->invoice) {
                $shipment->invoice()->delete();
            }

            $shipment->delete();
        }

        Shipment::where('assigned_to', $user->id)
            ->update(['assigned_to' => null]);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,staff,agent,customer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $plainPassword = $request->password;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        SendVerificationEmailJob::dispatch($user, $plainPassword);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully and verification email sent',
            'data' => $user
        ], 201);
    }

    public function updateRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,staff,customer,agent'
        ]);

        $user = User::find($request->user_id);
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $user
        ]);
    }

    public function editProfile(Request $request)
    {


        $user = User::with([
            'addresses' => function ($query) {
                $query->whereNull('recipient_id');
            }
        ])->findOrFail($request->user()->id);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'addresses' => 'required|array|min:1',
            'addresses.*.id' => 'nullable|exists:addresses,id',
            'addresses.*.address' => 'required|string|max:255',
            'addresses.*.city' => 'required|string|max:100',
            'addresses.*.state' => 'required|string|max:100',
            'addresses.*.country' => 'required|string|max:100',
            'addresses.*.zip_code' => 'required|string|max:20',
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone']
        ]);

        $submittedIds = collect($validated['addresses'])->pluck('id')->filter()->toArray();

        $user->addresses()
            ->whereNull('recipient_id')
            ->whereNotIn('id', $submittedIds)
            ->delete();

        foreach ($validated['addresses'] as $addressData) {
            if (!empty($addressData['id'])) {
                $address = $user->addresses()
                    ->whereNull('recipient_id')
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

            $user->addresses()->create([
                'address' => $addressData['address'],
                'city' => $addressData['city'],
                'state' => $addressData['state'],
                'country' => $addressData['country'],
                'zip_code' => $addressData['zip_code'],
                'recipient_id' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->load(['addresses' => function ($query) {
                $query->whereNull('recipient_id');
            }])
        ]);
    }
}
