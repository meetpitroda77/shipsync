@php
    $role = Auth::user()->role;
@endphp
@extends("layouts.{$role}")


@section('content')

    <div class="app-content">
        <div class="container-fluid">




            <p>View Payment Information </p>

            <form method="GET" action="{{ route("{$role}.shipment.paymentShipments") }}" class="mb-3 d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search "
                    value="{{ request('search') }}">

                <button class="btn btn-primary">Search</button>
            </form>

            <form method="GET" action="{{ route("{$role}.shipment.paymentShipments") }}">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-3">
                                <label class="form-label fw-bold"> Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>
                                        Paid
                                    </option>
                                    <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>
                                        Failed
                                    </option>


                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>

                                <a href="{{ route("{$role}.shipment.paymentShipments") }}" class="btn btn-secondary w-100">
                                    Clear
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive">

                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route("{$role}.shipment.paymentShipments", [
        'sort_field' => 'tracking_id',
        'sort_direction' => $sortField == 'tracking_id' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Tracking ID
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.shipment.paymentShipments", [
        'sort_field' => 'payment_method',
        'sort_direction' => $sortField == 'payment_method' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Payment Method
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.shipment.paymentShipments", [
        'sort_field' => 'payment_status',
        'sort_direction' => $sortField == 'payment_status' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Payment Status
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.shipment.paymentShipments", [
        'sort_field' => 'paid_at',
        'sort_direction' => $sortField == 'paid_at' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Paid At
                                </a>
                            </th>

                            <th>
                                amount
                            </th>

                            <th>transaction_id</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->tracking_id }}</td>
                                <td>{{ $payment->payment_method }}</td>
                                <td>{{ $payment->payment_status }}</td>
                                <td>
                                    {{ $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i') : 'N/A' }}
                                </td>
                                <td>{{ $payment->amount }}</td>
                                <td>{{ $payment->transaction_id }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No Payments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div>
                    {{ $payments->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection