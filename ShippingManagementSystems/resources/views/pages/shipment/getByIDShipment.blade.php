@php
    $role = Auth::user()->role;
@endphp
@extends('layouts.customer')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Shipment Details: {{ strtoupper($shipment->tracking_id) }}</h3>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-4">
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

                            <div class="col-md-4">
                                <h5>Receiver Information</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Name:</strong> {{ $shipment->receiver_name }}</li>
                                    <li><strong>Phone:</strong> {{ $shipment->receiver_phone }}</li>
                                    <li><strong>Address:</strong> {{ $shipment->receiverAddress->address ?? 'N/A' }},
                                        {{ $shipment->receiverAddress->city ?? '' }},
                                        {{ $shipment->receiverAddress->state ?? '' }},
                                        {{ $shipment->receiverAddress->country ?? '' }},
                                        {{ $shipment->receiverAddress->zip_code ?? '' }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h5>Status & Delivery</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Status:</strong> {{ ucfirst($shipment->status) }}</li>
                                    <li><strong>Delivery Method:</strong> {{ $shipment->delivery_method }}</li>
                                    <li><strong>Estimated Delivery:</strong> {{ $shipment->estimated_delivery_date }}</li>
                                    <li><strong>Actual Delivery:</strong> {{ $shipment->actual_delivery_date ?? 'N/A' }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row">

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Package Information</h5>
                                @if($shipment->packages->isNotEmpty())
                                    <table class="table table-bordered">
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
                                                    <td>{{ $package->length }} x {{ $package->width }} x {{ $package->height }} cm
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

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Invoice</h5>
                                @if($shipment->invoice)
                                    <a href="{{ route("{$role}.shipment.invoice", $shipment->id) }}" class="btn btn-primary">
                                        Download Invoice
                                    </a>
                                @else
                                    <p>No invoice generated for this shipment yet.</p>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Shipment Images</h5>
                                <div class="row">
                                    @foreach($shipment->images as $image)
                                        <div class="col-md-3">
                                            <img src="{{ Storage::url($image->image_path) }}" class="img-fluid"
                                                alt="Shipment Image">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection