@php
    $role = Auth::user()->role;
@endphp
@extends('layouts.customer')

@section('content')


    <div class="app-content">
        <div class="container-fluid">

            <h1> Edit Shipment</h1>
            <p>Update the details for your shipment</p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" enctype="multipart/form-data" action="{{ route("{$role}.updateShipment", $shipment) }}">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card card-primary card-outline mb-4">
                            <div class="card-header">
                                <div class="card-title">Sender Information</div>
                            </div>
                            <div class="card-body border-0">
                                <label for="sender_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('sender_name') is-invalid @enderror"
                                    name="sender_name" id="sender_name"
                                    value="{{ old('sender_name', $shipment->sender_name) }}" required>
                                @error('sender_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="sender_phone" class="form-label mt-2">Phone Number</label>
                                <input type="text" class="form-control @error('sender_phone') is-invalid @enderror"
                                    name="sender_phone" id="sender_phone"
                                    value="{{ old('sender_phone', $shipment->sender_phone) }}" required>
                                @error('sender_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="sender_address" class="form-label mt-2">Address</label>
                                <input type="text" class="form-control @error('sender_address') is-invalid @enderror"
                                    name="sender_address" id="sender_address" placeholder="123 Main St, City, State"
                                    value="{{ old('sender_address', $shipment->sender_address) }}" required>
                                @error('sender_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-primary card-outline mb-4">
                            <div class="card-header">
                                <div class="card-title">Receiver Information</div>
                            </div>
                            <div class="card-body border-0">
                                <label for="receiver_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('receiver_name') is-invalid @enderror"
                                    name="receiver_name" id="receiver_name"
                                    value="{{ old('receiver_name', $shipment->receiver_name) }}" required>
                                @error('receiver_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="receiver_phone" class="form-label mt-2">Phone Number</label>
                                <input type="text" class="form-control @error('receiver_phone') is-invalid @enderror"
                                    name="receiver_phone" id="receiver_phone"
                                    value="{{ old('receiver_phone', $shipment->receiver_phone) }}" required>
                                @error('receiver_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="receiver_address" class="form-label mt-2">Address</label>
                                <input type="text" class="form-control @error('receiver_address') is-invalid @enderror"
                                    name="receiver_address" id="receiver_address" placeholder="123 Main St, City, State"
                                    value="{{ old('receiver_address', $shipment->receiver_address) }}" required>
                                @error('receiver_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-primary card-outline mb-3">
                            <div class="card-header">
                                <div class="card-title">Package Details</div>
                            </div>
                            <div class="card-body">

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Package Type</label>
                                        <select name="package_type" required
                                            class="form-select @error('package_type') is-invalid @enderror">
                                            <option value="" selected disabled>-- Select Package Type --</option>

                                            <option value="document" {{ old('package_type', $shipment->package_type) == 'document' ? 'selected' : '' }}>Document</option>
                                            <option value="parcel" {{ old('package_type', $shipment->package_type) == 'parcel' ? 'selected' : '' }}>
                                                Parcel</option>
                                            <option value="box" {{ old('package_type', $shipment->package_type) == 'box' ? 'selected' : '' }}>Box
                                            </option>
                                            <option value="crate" {{ old('package_type', $shipment->package_type) == 'crate' ? 'selected' : '' }}>Crate
                                            </option>
                                            <option value="pallet" {{ old('package_type', $shipment->package_type) == 'pallet' ? 'selected' : '' }}>
                                                Pallet</option>
                                            <option value="fragile" {{ old('package_type', $shipment->package_type) == 'fragile' ? 'selected' : '' }}>
                                                Fragile Item</option>
                                            <option value="liquid" {{ old('package_type', $shipment->package_type) == 'liquid' ? 'selected' : '' }}>
                                                Liquid</option>
                                            <option value="perishable" {{ old('package_type', $shipment->package_type) == 'perishable' ? 'selected' : '' }}>Perishable Goods
                                            </option>
                                        </select>

                                        @error('package_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('weight') is-invalid @enderror" name="weight"
                                            value="{{ old('weight', $shipment->weight) }}" required>
                                        @error('weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Length (cm)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('length') is-invalid @enderror" name="length"
                                            value="{{ old('length', $shipment->length) }}" required>
                                        @error('length')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Width (cm)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('width') is-invalid @enderror" name="width"
                                            value="{{ old('width', $shipment->width) }}" required>
                                        @error('width')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Height (cm)</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('height') is-invalid @enderror" name="height"
                                            value="{{ old('height', $shipment->height) }}" required>
                                        @error('height')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label mt-3">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes"
                                            rows="3">{{ old('notes', $shipment->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label mt-3">Delivery Method</label>

                                        <select name="delivery_method" id="delivery_method"
                                            class="form-select @error('delivery_method') is-invalid @enderror"
                                            onchange="updateDelivery()" required>

                                            <option value="" selected disabled>Select Delivery Method</option>

                                            <option value="standard" {{ old('delivery_method', $shipment->delivery_method) == 'standard' ? 'selected' : '' }}>
                                                Standard (5 Days)
                                            </option>

                                            <option value="express" {{ old('delivery_method', $shipment->delivery_method) == 'express' ? 'selected' : '' }}>
                                                Express (2 Days)
                                            </option>

                                        </select>

                                        @error('delivery_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label mt-3">Estimated Delivery</label>
                                        <input type="text" class="form-control" id="estimated_delivery" readonly>
                                    </div>
                                    <input type="hidden" name="estimated_delivery_date" id="estimated_delivery_date">

                                    <div class="col-md-4">
                                        <label class="form-label mt-3">Cost</label>
                                        <input type="text" class="form-control" id="estimated_cost" readonly>
                                    </div>

                                    <div class="col-md-12">


                                        <label class="form-label mt-3">Package Photo</label>
                                        <input type="file" id="package_photos"
                                            class="form-control @error('package_photos') is-invalid @enderror"
                                            name="package_photos[]" multiple>
                                        @error('package_photos')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror





                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <button type="submit" class="btn btn-primary">Update Shipment</button>
            </form>
        </div>
    </div>

    <script>
        function updateDelivery() {
            const deliveryMethod = document.getElementById("delivery_method").value;
            const weightInput = document.querySelector('[name="weight"]').value;
            const estimatedCostField = document.getElementById("estimated_cost");
            const estimatedDeliveryField = document.getElementById("estimated_delivery");
            const estimatedDeliveryDateField = document.getElementById("estimated_delivery_date");

            if (!weightInput || parseFloat(weightInput) <= 0) {
                estimatedCostField.value = '';
                estimatedDeliveryField.value = '';
                estimatedDeliveryDateField.value = '';
                return;
            }

            const weight = parseFloat(weightInput);
            const baseCostINR = 500;
            const costPerKgINR = 100;
            const multiplier = deliveryMethod === "express" ? 1.5 : 1;
            const totalINR = (baseCostINR + weight * costPerKgINR) * multiplier;

            const d = new Date();
            const days = deliveryMethod === "express" ? 2 : 5;
            d.setDate(d.getDate() + days);
            const day = ("0" + d.getDate()).slice(-2);
            const mon = ("0" + (d.getMonth() + 1)).slice(-2);
            const year = d.getFullYear();

            estimatedDeliveryField.value = `${day}-${mon}-${year} (${days} Days)`;
            estimatedDeliveryDateField.value = `${year}-${mon}-${day}`;
            estimatedCostField.value = `₹${totalINR.toFixed(2)}`;
        }

        document.getElementById("delivery_method").onchange = updateDelivery;
        document.querySelector('[name="weight"]').oninput = updateDelivery;

        document.addEventListener("DOMContentLoaded", function () {
            updateDelivery();
        });

    </script>



    <script>

        let selectedFiles = [];

        const input = document.getElementById('package_photos');

        input.addEventListener('change', function (e) {

            const newFiles = Array.from(e.target.files);

            newFiles.forEach(file => {
                selectedFiles.push(file);
            });

            updateInput();

        });


        function updateInput() {

            let dt = new DataTransfer();

            selectedFiles.forEach(file => {
                dt.items.add(file);
            });

            input.files = dt.files;

        }

    </script>
@endsection