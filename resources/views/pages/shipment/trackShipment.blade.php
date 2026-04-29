@php
    $role = Auth::check() ? Auth::user()->role : null;
    $layout = $role ? "layouts.$role" : "layouts.landinglayout";
@endphp

@extends($layout)
@section('content')


<style>
.stepper {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 60px auto;
    max-width: 1000px;
}

.step {
    text-align: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.step:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 5px;
    background: #d3d3d3;
    z-index: -1;
    transform: translateX(0%);
}

.step.completed:not(:last-child)::after {
    background: #28a745;
}

.step-circle {
    width: 40px;
    height: 40px;
    background: #c97474;
    border-radius: 50%;
    line-height: 40px;
    color: white;
    margin: auto;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.step.completed .step-circle {
    background: #28a745;
}

.step.active .step-circle {
    background: #0d6efd;
}

.step-label {
    margin-top: 10px;
    font-size: 12px;
}

@media (max-width:768px) {
    .stepper {
        flex-direction: column;
        align-items: flex-start;
        margin-left: 40px;
    }

    .step {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
    }

    .step:not(:last-child)::after {
        top: 40px;
        left: 28px;
        width: 4px;
        height: 100%;
    }

    .step-circle {
        margin: 0;
        margin-right: 15px;
    }

    .step-label {
        margin-top: 0;
        font-size: 14px;
    }
}
</style>
    <div class="container-fluid mt-3">


        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card border-0">
                    <div class="card-header">
                        <h3 class="card-title">Track Your Shipment</h3>
                    </div>






                    <input type="hidden" id="trackingID" value="{{ $shipment->tracking_id ?? '' }}">
                    <div class="card-body">
                        <form action="{{$role ? route("{$role}.shipment.track") : route('shipment.track') }}" method="get">


                            <div class="form-group">
                                <div class="input-group">

                                    <input type="text" name="tracking_id" value="{{ request('tracking_id') }}"
                                        id="tracking_id" class="form-control" placeholder="Enter tracking ID">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Track</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(isset($shipment))
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Shipment Details: {{ $shipment->tracking_id }}</h3>
                        </div>
                        <div class="card-body">
                            @php
                                $status = $shipment->status;

                                $allStatuses = [
                                    'created' => 'Created',
                                ];

                                $logStatuses = $shipment->logs->pluck('status')->toArray();



                                if (in_array($status, ['pending_assigned', 'pending_payment'])) {
                                    $allStatuses[$status] = $status == 'pending_assigned' ? 'Pending Assigned' : 'Pending Payment';
                                }

                                if (in_array('assigned', $logStatuses)) {
                                    $allStatuses['assigned'] = 'Assigned';

                                }

                                if (in_array('picked_up', $logStatuses)) {
                                    $allStatuses['picked_up'] = 'Picked Up';
                                }
                                if (in_array('in_transit', $logStatuses)) {
                                    $allStatuses['in_transit'] = 'In Transit';
                                }

                                if (in_array('out_for_delivery', $logStatuses)) {
                                    $allStatuses['out_for_delivery'] = 'Out for Delivery';
                                }




                                $finalStatus = 'delivered';
                                if (in_array($status, ['failed_delivery', 'delayed', 'canceled'])) {
                                    $finalStatus = $status;
                                }
                                $allStatuses[$finalStatus] = match ($finalStatus) {
                                    'delivered' => 'Delivered',
                                    'failed_delivery' => 'Failed Delivery',
                                    'delayed' => 'Delayed',
                                    'canceled' => 'Canceled',
                                };

                                $currentIndex = array_search($status, array_keys($allStatuses));
                            @endphp

                            <div class="stepper row">
                                @foreach($allStatuses as $key => $label)
                                    @php
                                        $stepIndex = array_search($key, array_keys($allStatuses));

                                        if ($key === 'delivered' && $status === 'delivered') {
                                            $class = 'completed';
                                        } elseif ($stepIndex < $currentIndex) {
                                            $class = 'completed';
                                        } elseif ($stepIndex == $currentIndex) {
                                            $class = 'active';
                                        } else {
                                            $class = '';
                                        }
                                    @endphp
                                    <div class="step {{ $class }}" data-status="{{ $key }}">
                                        <div class="step-circle">
                                            @if($class == 'completed')
                                                <i class="bi bi-check "></i>

                                            @elseif($key == 'created')
                                                <i class="bi bi-box"></i>

                                            @elseif($key == 'pending_assigned')
                                                <i class="bi bi-hourglass-split"></i>

                                            @elseif($key == 'pending_payment')
                                                <i class="bi bi-credit-card"></i>

                                            @elseif($key == 'assigned')
                                                <i class="bi bi-person-check"></i>

                                            @elseif($key == 'picked_up')
                                                <i class="bi bi-box-seam"></i>

                                            @elseif($key == 'in_transit')
                                                <i class="bi bi-truck"></i>

                                            @elseif($key == 'out_for_delivery')
                                                <i class="bi bi-truck-flatbed"></i>

                                            @elseif($key == 'delivered')
                                                <i class="bi bi-check-circle"></i>

                                            @elseif($key == 'failed_delivery')
                                                <i class="bi bi-x-circle"></i>

                                            @elseif($key == 'delayed')
                                                <i class="bi bi-clock-history"></i>

                                            @elseif($key == 'canceled')
                                                <i class="bi bi-slash-circle"></i>
                                            @endif
                                        </div>
                                        <div class="step-label">{{ $label }}</div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($shipment->status == 'failed_delivery')
                                @php
                                    $failedLog = $shipment->logs->where('status', 'failed_delivery')->first();
                                @endphp
                                <div class="alert alert-info" role="alert">

                                    <h4 class="alert-heading">
                                        {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                    </h4>

                                    @if($shipment->status === 'failed_delivery')
                                        <p>
                                            <strong>Reason:</strong>
                                            {{ $failedLog->description ?? 'No reason provided' }}
                                        </p>

                                    @endif

                                </div>
                            @endif
                            <div class="row ">
                                <div class="col-md-4 col-sm-6 col-12" >
                                    <h5>Sender Information</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Name:</strong> {{ $shipment->sender_name }}</li>
                                        <li><strong>Phone:</strong> {{ $shipment->sender_phone }}</li>
                                        <li><strong>Address:</strong> {{ $shipment->senderAddress->address ?? 'N/A' }},
                                            {{ $shipment->senderAddress->city ?? '' }},
                                            {{ $shipment->senderAddress->state ?? '' }},
                                            {{ $shipment->senderAddress->country ?? '' }},
                                            {{ $shipment->senderAddress->zip_code ?? '' }}

                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4 col-sm-6 col-12">
                                    <h5>Receiver Information</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Name:</strong> {{ $shipment->receiver_name }}</li>
                                        <li><strong>Phone:</strong> {{ $shipment->receiver_phone }}</li>
                                        <li><strong>Address:
                                            </strong> {{ $shipment->receiverAddress->address ?? 'N/A' }},
                                            {{ $shipment->receiverAddress->city ?? '' }},
                                            {{ $shipment->receiverAddress->state ?? '' }},
                                            {{ $shipment->receiverAddress->country ?? '' }},
                                            {{ $shipment->receiverAddress->zip_code ?? '' }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-4 col-sm-6 col-12">
                                    <h5>Status & Delivery</h5>
                                    <ul class="list-unstyled">
                                        <li>
                                            <strong>Status:</strong>

                                            <span id="statusText">

                                                {{ ucfirst($shipment->status) }}

                                            </span>

                                        </li>
                                        <li><strong>Delivery Method:</strong> {{ $shipment->delivery_method }}</li>
                                        <li><strong>Estimated Delivery:</strong> {{ $shipment->estimated_delivery_date }}</li>
                                        <li><strong>Actual Delivery:</strong> {{ $shipment->actual_delivery_date ?? 'N/A' }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h5>Package Information</h5>
                                    @if($shipment->packages->isNotEmpty())
                                        <div class="table-responsive">

                                            <table class="table table-bordered text-center">
                                                <thead>
                                                    <tr>
                                                        <th>Description</th>
                                                        <th>Weight (kg)</th>
                                                        <th>Dimensions (L x W x H)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($shipment->packages as $package)
                                                        <tr>
                                                            <td>{{ $package->description }}</td>
                                                            <td>{{ $package->weight }} kg</td>
                                                            <td>{{ $package->length }} x {{ $package->width }} x {{ $package->height }}
                                                                cm
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                    @else
                                            <p>No package information available.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h5>Invoice</h5>
                                    @if($shipment->invoice)
                                        <a href="{{ $role ? route("{$role}.shipment.invoice", $shipment->id) : route('shipment.invoice', $shipment->id) }}"
                                            class="btn btn-primary">
                                            Download Invoice
                                        </a>
                                    @else
                                        <p>No invoice generated for this shipment yet.</p>
                                    @endif
                                </div>
                            </div>

                            {{-- <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table  table-striped table-responsive">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Description</th>
                                                    <th>Timestamp</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($shipment->logs as $log)
                                                <tr>
                                                    <td>{{ ucfirst($log->status) }}</td>
                                                    <td>{{ $log->description }}</td>
                                                    <td>{{ $log->created_at->format('d M, Y H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div> --}}




                        </div>
                    </div>
                @elseif(isset($error))
                    <div class="alert alert-danger mt-4">
                        <strong>Error:</strong> {{ $error }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection