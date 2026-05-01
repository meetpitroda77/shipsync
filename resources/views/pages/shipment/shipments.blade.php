@php
    $role = Auth::user()->role;
@endphp
@extends("layouts.{$role}")

@section('content')
    <div class="app-content">
        <div class="container-fluid">

<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div class="toast-container position-fixed top-0 end-0 p-3">

        <div id="statusupdate" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>

    </div>
</div>


            <p>View and manage your shipments</p>


            <form method="GET" action="{{ route("{$role}.shipments") }}" class="mb-3 d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search "
                    value="{{ request('search') }}">

                <button class="btn btn-primary">Search</button>
            </form>
            <form method="GET" action="{{ route("{$role}.shipments") }}">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Shipment Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="delivered" {{ $status == 'delivered' ? 'selected' : '' }}>
                                        Delivered
                                    </option>
                                    <option value="in_transit" {{ $status == 'in_transit' ? 'selected' : '' }}>
                                        In Transit
                                    </option>
                                    'pending_assigned',
                                    <option value="pending_assigned" {{ $status == 'pending_assigned' ? 'selected' : '' }}>
                                        Pending Assigned
                                    </option>
                                    <option value="pending_payment" {{ $status == 'pending_payment' ? 'selected' : '' }}>
                                        Pending Payment
                                    </option>
                                    <option value="assigned" {{ $status == 'assigned' ? 'selected' : '' }}>
                                        Assigned
                                    </option>
                                    <option value="picked_up" {{ $status == 'picked_up' ? 'selected' : '' }}>
                                        Picked Up
                                    </option>
                                    <option value="in_transit" {{ $status == 'in_transit' ? 'selected' : '' }}>
                                        In Transit
                                    </option>
                                    <option value="out_for_delivery" {{ $status == 'out_for_delivery' ? 'selected' : '' }}>
                                        Out for Delivery
                                    </option>
                                    <option value="delivered" {{ $status == 'delivered' ? 'selected' : '' }}>
                                        Delivered
                                    </option>
                                    <option value="failed_delivery" {{ $status == 'failed_delivery' ? 'selected' : '' }}>
                                        Failed Delivery
                                    </option>
                                    <option value="delayed" {{ $status == 'delayed' ? 'selected' : '' }}>
                                        Delayed
                                    </option>
                                    <option value="canceled" {{ $status == 'canceled' ? 'selected' : '' }}>
                                        Canceled
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Delivery Method</label>
                                <select name="delivery_method" class="form-select">
                                    <option value="">All Methods</option>
                                    <option value="standard" {{ $deliveryMethod == 'standard' ? 'selected' : '' }}>
                                        Standard
                                    </option>
                                    <option value="express" {{ $deliveryMethod == 'express' ? 'selected' : '' }}>
                                        Express
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>

                                <a href="{{ route("{$role}.shipments") }}" class="btn btn-secondary w-100">
                                    Clear
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route("{$role}.shipments", [
        'sort_field' => 'tracking_id',
        'sort_direction' => $sortField == 'tracking_id' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Tracking ID
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.shipments", [
        'sort_field' => 'sender_name',
        'sort_direction' => $sortField == 'sender_name' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Sender
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.shipments", [
        'sort_field' => 'receiver_name',
        'sort_direction' => $sortField == 'receiver_name' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Receiver
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.shipments", [
        'sort_field' => 'status',
        'sort_direction' => $sortField == 'status' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Status
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.shipments", [
        'sort_field' => 'created_at',
        'sort_direction' => $sortField == 'created_at' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Created At
                                </a>
                            </th>

                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($shipments as $shipment)
                                        <tr data-id="{{ $shipment->id }}">
                                            <td>{{ $shipment->tracking_id }}</td>
                                            <td>{{ $shipment->sender_name }}</td>
                                            <td>{{ $shipment->receiver_name }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $shipment->status == 'pending' ? 'warning' :
                            ($shipment->status == 'in_transit' ? 'info' :
                                ($shipment->status == 'delivered' ? 'success' : 'secondary'))
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }}">
                                                    {{ ucfirst($shipment->status ?? 'pending') }}
                                                </span>
                                            </td>
                                            <td>{{ $shipment->created_at->format('Y-m-d') }}</td>
                                            <td class="d-flex gap-2">
                                                @if(in_array($role, ['staff', 'agent', 'admin']))
                                                    {{-- <a href="{{ route(" {$role}.editShipment", $shipment) }}"
                                                        class="btn btn-sm btn-outline-primary">Edit</a> --}}
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                        data-bs-target="#manageModal{{ $shipment->id }}">Manage</button>

                                                @endif
                                                <a href="{{ route("{$role}.shipments.show", $shipment) }}"
                                                    class="btn btn-sm btn-outline-primary align-content-center">View</a>

                                                @if(in_array($role, ['customer', 'admin']))

                                                    @if(in_array($role, ['admin']))

                                                        {{-- <a href="{{ route("{$role}.editShipment", $shipment) }}"
                                                            class="btn btn-sm btn-outline-primary">Edit</a> --}}

                                                    @endif


                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $shipment->id }}">
                                                        Delete
                                                    </button>
                                                    @if($shipment->status === 'pending_payment')
                                                        <form action="{{ route("{$role}.payShipment", $shipment) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success ">Pay Now</button>
                                                        </form>
                                                    @endif

                                                @endif


                                                @if(in_array($role, ['customer', 'admin']))

                                                    <div class="modal fade" id="deleteModal{{ $shipment->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <form action="{{ route("{$role}.destroyShipment", $shipment) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Are you sure you want to delete shipment {{ $shipment->tracking_id }}?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                @endif


                                                @if(in_array($role, ['staff', 'admin', 'agent']))
                                                    <div class="modal fade" id="manageModal{{ $shipment->id }}" tabindex="-1"
                                                        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Manage Shipments</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form class="updateStatusForm" data-id="{{ $shipment->id }}"
                                                                    {{-- action="{{ route("{$role}.shipment.UpdateStatus", ['shipment' => $shipment->id]) }}" --}}
                                                                    {{-- method="POST" enctype="multipart/form-data" --}}
                                                                    >
                                                                     @csrf
                                                                    @method('PATCH')

                                                                    <div class="modal-body">
                                                                        @if(in_array($role, ['staff', 'admin']))

                                                                            <label for="agentid" class="mt-3">Shipment Assigned to Delivery
                                                                                Agent</label>
                                                                            <select id="agentid" name="agentid"
                                                                                class="form-select mt-1 @error('agentid') is-invalid @enderror"
                                                                                aria-label="Select Agent">
                                                                                <option selected disabled>Select Agent</option>
                                                                                @foreach ($users as $user)
                                                                                    <option value="{{ $user->id }}" {{ old('agentid', $shipment->assigned_to) == $user->id ? 'selected' : '' }}>
                                                                                        {{ $user->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                            @error('agentid')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        @endif

                                                                        <label for="status" class="mt-3">Change Status</label>
                                                                        <select name="status"
                                                                            class="form-select mt-1  status-select @error('status') is-invalid @enderror">
                                                                            <option selected disabled>Open this select Status</option>
                                                                            @if ($role != "agent")

                                                                                <option value="assigned" {{ old('status') == 'assigned' ? 'selected' : '' }}>assigned</option>
                                                                            @endif
                                                                            <option value="picked_up" {{ old('status') == 'picked_up' ? 'selected' : '' }}>picked up</option>
                                                                            <option value="in_transit" {{ old('status') == 'in_transit' ? 'selected' : '' }}>in transit</option>
                                                                            <option value="out_for_delivery" {{ old('status') == 'out_for_delivery' ? 'selected' : '' }}>out for
                                                                                delivery</option>
                                                                            <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>delivered</option>
                                                                            <option value="delayed" {{ old('status') == 'delayed' ? 'selected' : '' }}>delayed</option>
                                                                            <option value="failed_delivery" {{ old('status') == 'failed_delivery' ? 'selected' : '' }}>failed delivery</option>
                                                                            @if ($role != "agent")
                                                                                <option value="canceled" {{ old('status') == 'canceled' ? 'selected' : '' }}>canceled</option>
                                                                            @endif
                                                                        </select>
                                                                        @error('status')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror

                                                                        <div class="delivery-proof" style="display:none;">
                                                                            <label for="delivery_proof" class="mt-3">Upload Delivery Proof
                                                                                (Image)</label>
                                                                            <input type="file" name="delivery_proof" id="delivery_proof"
                                                                                class="form-control mt-1  @error('delivery_proof') is-invalid @enderror"
                                                                                accept="image/*">
                                                                            @error('delivery_proof')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                        <div class="failed-reason" style="display:none;">
                                                                            <label class="mt-3">Failure Reason</label>
                                                                            <textarea name="failed_reason"
                                                                                class="form-control @error('failed_reason') is-invalid @enderror"
                                                                                placeholder="Enter reason"></textarea>

                                                                            @error('failed_reason')
                                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                            @enderror
                                                                        </div>
                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">Cancel</button>

                                                                        {{-- @if(in_array($role, ['staff', 'admin']))
                                                                            <button type="submit" class="btn btn-danger">Assign Agent</button>
                                                                        @endif --}}

                                                                        @if(in_array($role, ['agent','staff', 'admin']))
                                                                            <button type="submit" class="btn btn-danger">Update Status</button>
                                                                        @endif
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No shipments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            <div>
                {{ $shipments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script>

        document.addEventListener("DOMContentLoaded", function () {

            document.querySelectorAll('.status-select').forEach(select => {

                select.addEventListener('change', function () {

                    let modal = this.closest('.modal');
                    let deliveryProof = modal.querySelector('.delivery-proof');
                    let failedReason = modal.querySelector('.failed-reason');


                    if (this.value === 'delivered') {
                        deliveryProof.style.display = 'block';
                    } else {
                        deliveryProof.style.display = 'none';
                    }
                    if (this.value === 'failed_delivery') {
                        failedReason.style.display = 'block';
                    } else {
                        failedReason.style.display = 'none';
                    }


                });

            });

        });

    </script>

<script>
const token = "{{ session('api_token') }}";
var role="{{ $role }}";
</script>
@endsection