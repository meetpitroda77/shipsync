@extends('layouts.admin')


@section("content")
    <div class="app-content">
        <div class="container-fluid">




            <p>View Daily Reports </p>

            <form method="GET" action="{{ route("admin.reports") }}">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">


                            <div class="col-md-5">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>

                                <a href="{{ route("admin.reports") }}" class="btn btn-secondary w-100">
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
                                Report Date
                            </th>

                            <th>
                                Total Shipments
                            </th>

                            <th>

                                Total Revenue
                            </th>
                            <th>Delivered</th>

                            <th>

                                Pending Assigned
                            </th>

                            <th>
                                Pending Payment
                            </th>

                            <th>Picked Up</th>
                            <th>In Transit</th>
                            <th>Out for Delivery</th>
                            <th>Failed Delivery</th>
                            <th>Delayed</th>
                            <th>Canceled</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $report->report_date }}</td>
                                <td>{{ $report->total_shipments }}</td>
                                <td>{{ $report->total_revenue }}</td>
                                <td>{{ $report->delivered }}</td>
                                <td>{{ $report->pending_assigned }}</td>
                                <td>{{ $report->pending_payment }}</td>
                                <td>{{ $report->picked_up }}</td>
                                <td>{{ $report->in_transit }}</td>
                                <td>{{ $report->out_for_delivery }}</td>
                                <td>{{ $report->failed_delivery }}</td>
                                <td>{{ $report->delayed }}</td>
                                <td>{{ $report->canceled }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No Reports found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                </table>

            </div>

            <div>
                {{ $reports->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection