@php
    $role = Auth::user()->role;
@endphp
@extends("layouts.{$role}")

@section('content')
    <div class="app-content">
        <div class="container-fluid">




            <p></p>



            <form method="GET" action="{{ route("reportgeneral") }}">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="shipment g-3 align-items-end">
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
                                    <input type="date" name="from" value="{{ $startDate }}" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-bold">End Date</label>
                                    <input type="date" name="to" value="{{ $endDate }}" class="form-control">
                                </div>

                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>

                                    <a href="{{ route("reportgeneral") }}" class="btn btn-secondary w-100">
                                        Clear
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-end gap-2 mb-2">
                <a href="{{ route('report.pdf', request()->all()) }}" class="btn btn-danger">
                     Pdf
                </a>

                <a href="{{ route('report.excel', request()->all()) }}" class="btn btn-success">
                     Excel
                </a>
                 <a href="{{ route('report.csv', request()->all()) }}" class="btn btn-info">
                     CSV
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>
                                Tracking ID
                            </th>

                            <th>
                                Date
                            </th>
                            <th>
                                Sender
                            </th>

                            <th>
                                Origin
                            </th>

                            <th>
                                Status
                            </th>
                            <th>Weight</th>
                            <th>Subtotal</th>
                            <th>Insurance</th>
                            <th>Tax</th>
                            <th>Total</th>

                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($report as $shipment)
                            <tr>
                                <td>{{ $shipment['tracking'] }}</td>
                                <td>{{ $shipment['date'] }}</td>
                                <td>{{ $shipment['sender'] }}</td>
                                <td>{{ $shipment['origin'] }}</td>
                                <td>{{ $shipment['status'] }}</td>
                                <td>{{ $shipment['weight'] }}</td>
                                <td>{{ $shipment['subtotal'] }}</td>
                                <td>{{ $shipment['insurance'] }}</td>
                                <td>{{ $shipment['tax'] }}</td>
                                <td>{{ $shipment['total'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center">No shipments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-center">Total</th>
                            <th>{{ $totals['weight'] }}</th>
                            <th>{{ $totals['subtotal']}}</th>
                            <th>{{ $totals['insurance'] }}</th>
                            <th>{{ $totals['tax'] }}</th>
                            <th>${{ $totals['total'] }}</th>
                        </tr>
                    </tfoot>


                </table>

            </div>

            <div>
                {{ $shipments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>




@endsection