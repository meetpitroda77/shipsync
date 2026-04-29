@php
    $role = Auth::user()->role;


    use App\Services\SettingService;

    $pricePerKg = (float) SettingService::get('price_per_kg', 3);
    $taxPercent = (float) SettingService::get('tax_percent', 0.19);
    $insurance = (float) SettingService::get('insurance', 1);
    $expressMultiplier = (float) SettingService::get('express_multiplier', 1.5);
@endphp
@extends('layouts.customer')

@section('content')

    <div class="app-content">
        <div class="container-fluid">

            <p>Fill in the details to create a shipment request</p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form id="shipmentForm" enctype="multipart/form-data">
                @csrf
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
                                    value="{{ old('sender_name', Auth::user()->name) }}">
                                @error('sender_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="sender_phone" class="form-label mt-2">Phone Number</label>
                                <input type="text" class="form-control @error('sender_phone') is-invalid @enderror"
                                    name="sender_phone" id="sender_phone" readonly
                                    value="{{ old('sender_phone', Auth::user()->phone) }}">
                                @error('sender_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="sender_address" class="form-label mt-2">Address</label>
                                <select class="form-control @error('sender_address_id') is-invalid @enderror"
                                    name="sender_address_id" id="sender_address">
                                    <option value="">Select an Address</option>
                                    @foreach ($sender->addresses as $address)
                                        <option value="{{ $address->id }}" {{ old('sender_address_id') == $address->id ? 'selected' : '' }}>
                                            {{ $address->address }} - {{ $address->city }}, {{ $address->state }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sender_address_id')
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
                                <select class="form-select" name="receiver_name" id="receiver_name"
                                    data-old-recipient="{{ old('receiver_name') }}"
                                    data-old-address="{{ old('receiver_address_id') }}">
                                    <option value="">Select a Name</option>

                                    @foreach ($recipients as $recipient)

                                        <option value="{{ $recipient->id }}" {{ old('receiver_name') == $recipient->id ? 'selected' : '' }}>

                                            {{ $recipient->receiver_name }}

                                        </option>

                                    @endforeach

                                </select>
                                @error('receiver_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="receiver_phone" class="form-label mt-2">Phone Number</label>
                                <input type="text" name="receiver_phone" id="receiver_phone" readonly
                                    class="form-control @error('receiver_phone') is-invalid @enderror"
                                    value="{{ old('receiver_phone') }}" placeholder="Enter Phone Number">
                                @error('receiver_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="receiver_address" class="form-label mt-2">Address</label>
                                <select name="receiver_address_id" id="receiver_address"
                                    class="form-control @error('receiver_address_id') is-invalid @enderror">

                                    <option value="">Select an Address</option>

                                </select>

                                @error('receiver_address_id')
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
                                    <div class="col-md-12">
                                        <label class="form-label">Package Type</label>
                                        <select name="package_type"
                                            class="form-control @error('package_type') is-invalid @enderror">
                                            <option value="">-- Select Package Type --</option>

                                            <option value="document" {{ old('package_type') == 'document' ? 'selected' : '' }}>Document</option>
                                            <option value="parcel" {{ old('package_type') == 'parcel' ? 'selected' : '' }}>
                                                Parcel</option>
                                            <option value="box" {{ old('package_type') == 'box' ? 'selected' : '' }}>Box
                                            </option>
                                            <option value="crate" {{ old('package_type') == 'crate' ? 'selected' : '' }}>
                                                Crate
                                            </option>
                                            <option value="pallet" {{ old('package_type') == 'pallet' ? 'selected' : '' }}>
                                                Pallet</option>
                                            <option value="fragile" {{ old('package_type') == 'fragile' ? 'selected' : '' }}>
                                                Fragile Item</option>
                                            <option value="liquid" {{ old('package_type') == 'liquid' ? 'selected' : '' }}>
                                                Liquid</option>
                                            <option value="perishable" {{ old('package_type') == 'perishable' ? 'selected' : '' }}>Perishable Goods</option>
                                        </select>

                                        @error('package_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div id="imagePreview" class="mt-3 d-flex gap-2 flex-wrap"></div>
                                    <div class="col-md-12">
                                        <label class="form-label mt-3">Package Photo</label>
                                        <input type="file" id="package_photos"
                                            class="form-control @error('package_photos') is-invalid @enderror"
                                            name="package_photos[]" multiple accept="image/*">
                                        @error('package_photos')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror




                                    </div>



                                    <div class="col-12">
                                        <label class="form-label mt-3">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes"
                                            rows="3">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label mt-3">Courier Company</label>

                                        <select name="courier_company"
                                            class="form-control @error('courier_company') is-invalid @enderror">

                                            <option value="">Select Courier</option>

                                            <option value="dhl" {{ old('courier_company') == 'dhl' ? 'selected' : '' }}>
                                                DHL
                                            </option>

                                            <option value="fedex" {{ old('courier_company') == 'fedex' ? 'selected' : '' }}>
                                                FedEx
                                            </option>

                                            <option value="bluedart" {{ old('courier_company') == 'bluedart' ? 'selected' : '' }}>
                                                BlueDart
                                            </option>

                                            <option value="delhivery" {{ old('courier_company') == 'delhivery' ? 'selected' : '' }}>
                                                Delhivery
                                            </option>

                                        </select>

                                        @error('courier_company')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>



                                    <div class="col-md-6">

                                        <label class="form-label mt-3">
                                            Shipping Mode
                                        </label>

                                        <select name="shipping_mode"
                                            class="form-control @error('shipping_mode') is-invalid @enderror">

                                            <option value="">
                                                Select Mode
                                            </option>

                                            <option value="air" {{ old('shipping_mode') == 'air' ? 'selected' : '' }}>
                                                Air
                                            </option>

                                            <option value="surface" {{ old('shipping_mode') == 'surface' ? 'selected' : '' }}>
                                                Surface
                                            </option>

                                            <option value="rail" {{ old('shipping_mode') == 'rail' ? 'selected' : '' }}>
                                                Rail
                                            </option>

                                        </select>

                                        @error('shipping_mode')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>





                                    <div id="packages-container">



                                        @php
                                            $oldPackages = old('package_amount', [1]);
                                        @endphp

                                        <div id="package-list">

                                            @foreach($oldPackages as $index => $val)

                                                <div class="package-row mb-3 border p-3 rounded">


                                                    <button type="button"
                                                        class="btn btn-danger btn-sm  remove-package  d-flex  ms-auto mb-2 ">

                                                        Remove Package

                                                    </button>


                                                    <div class="row g-3">

                                                        <div class="col-md-1">
                                                            <label>Qty</label>

                                                            <input type="number" name="package_amount[]"
                                                                class="form-control @error('package_amount.' . $index) is-invalid @enderror"
                                                                value="{{ old('package_amount.' . $index, 1) }}" min="1">

                                                            @error('package_amount.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror

                                                        </div>


                                                        <div class="col-md-5">

                                                            <label>Package Description</label>

                                                            <input type="text" name="package_description[]"
                                                                class="form-control @error('package_description.' . $index) is-invalid @enderror"
                                                                value="{{ old('package_description.' . $index) }}">

                                                            @error('package_description.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror

                                                        </div>


                                                        <div class="col-md-3">

                                                            <label>Weight</label>

                                                            <input type="number" name="package_weight[]"
                                                                class="form-control @error('package_weight.' . $index) is-invalid @enderror"
                                                                value="{{ old('package_weight.' . $index) }}">

                                                            @error('package_weight.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror

                                                        </div>


                                                        <div class="col-md-3">

                                                            <label>Length</label>

                                                            <input type="number" name="package_length[]"
                                                                class="form-control @error('package_length.' . $index) is-invalid @enderror"
                                                                value="{{ old('package_length.' . $index) }}">

                                                            @error('package_length.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror

                                                        </div>




                                                    </div>


                                                    <div class="row g-3 mt-1 align-items-end">

                                                        <div class="col-md-3">

                                                            <label>Height</label>

                                                            <input type="number" name="package_height[]"
                                                                class="form-control @error('package_height.' . $index) is-invalid @enderror"
                                                                value="{{ old('package_height.' . $index) }}">

                                                            @error('package_height.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror

                                                        </div>
                                                        <div class="col-md-3">

                                                            <label>Width</label>

                                                            <input type="number" name="package_width[]"
                                                                class="form-control @error('package_width.' . $index) is-invalid @enderror"
                                                                value="{{ old('package_width.' . $index) }}">

                                                            @error('package_width.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror

                                                        </div>




                                                    </div>




                                                </div>


                                            @endforeach

                                        </div>

                                        <div class="d-flex justify-content-start mb-2">
                                            <button type="button" class="btn btn-primary btn-sm" id="add-package">
                                                Add Another Package
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label mt-3">Delivery Method</label>

                                            <select name="delivery_method" id="delivery_method"
                                                class="form-select @error('delivery_method') is-invalid @enderror"
                                                onchange="updateDelivery()">

                                                <option value="">Select Delivery Method</option>

                                                <option value="standard" {{ old('delivery_method') == 'standard' ? 'selected' : '' }}>
                                                    Standard (5 Days)
                                                </option>

                                                <option value="express" {{ old('delivery_method') == 'express' ? 'selected' : '' }}>
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
                                            <label class="form-label mt-3">Cost ($)</label>
                                            <input type="text" class="form-control" id="estimated_cost" readonly>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="container mt-5">
                                <table class="table ">
                                    <thead>
                                        <tr>
                                            <th scope="col">Description</th>
                                            <th scope="col">Amount ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Subtotal</td>
                                            <td id="subtotal">0.00</td>
                                        </tr>

                                        <tr>
                                            <td>Shipping Insurance </td>
                                            <td>1</td>
                                        </tr>

                                        <tr>
                                            <td>Tax {{ $taxPercent*100 }} %</td>
                                            <td id="tax">0.00</td>
                                        </tr>

                                        <tr class="table-active">
                                            <td><strong>Total</strong></td>
                                            <td id="total"><strong>0.00</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>


                    </div>



                </div>

                <button type="submit" class="btn btn-primary">Create Shipment and pay now</button>
            </form>

            <div class="modal fade" id="previewModal">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content  custom-modal-bg ">

                        <div class="modal-header">
                            <h5 class="modal-title">Shipment Preview</h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="card p-3 border-0 shadow-sm bg-info bg-opacity-10">
                                        <h6 class="text-uppercase text-muted">Sender</h6>

                                        <p><i class="bi bi-person"></i> <strong id="p_sender_name"></strong></p>
                                        <p><i class="bi bi-telephone"></i> <span id="p_sender_phone"></span></p>
                                        <p><i class="bi bi-geo-alt"></i> <span id="p_sender_address"></span></p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card p-3 border-0 shadow-sm bg-info bg-opacity-10">
                                        <h6 class="text-uppercase text-muted">Receiver</h6>

                                        <p><i class="bi bi-person"></i> <strong id="p_receiver_name"></strong></p>
                                        <p><i class="bi bi-telephone"></i> <span id="p_receiver_phone"></span></p>
                                        <p><i class="bi bi-geo-alt"></i> <span id="p_receiver_address"></span></p>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="row text-center">

                                <div class="col-md-3">
                                    <div class="card p-2 border-0 shadow-sm bg-info bg-opacity-10">
                                        <small>Packages</small>
                                        <h6 id="p_package_count"></h6>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card p-2 border-0 shadow-sm bg-info bg-opacity-10">
                                        <small>Total Weight</small>
                                        <h6 id="p_total_weight"></h6>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card p-2 border-0 shadow-sm bg-info bg-opacity-10">
                                        <small>Delivery</small>
                                        <h6 id="p_delivery"></h6>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card p-2 border-0 shadow-sm bg-info bg-opacity-10">
                                        <small>Est Date</small>
                                        <h6 id="p_delivery_date"></h6>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <h6>Packages</h6>
                            <table class="table table-bordered table-sm  table-info bg-opacity-10 ">
                                <thead class=" ">
                                    <tr>
                                        <th>#</th>
                                        <th>Qty</th>
                                        <th>Weight</th>
                                        <th>Dimensions</th>
                                    </tr>
                                </thead>
                                <tbody id="p_packages" class="bg-info bg-opacity-10"></tbody>
                            </table>

                            <hr>

                            <div class="row text-center">
                                <div class="col-md-4"><strong>Subtotal:</strong> $<span id="p_subtotal"></span></div>
                                <div class="col-md-4"><strong>Tax:</strong> $<span id="p_tax"></span></div>
                                <div class="col-md-4 "><strong>Total:</strong> $<span id="p_total"></span></div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Edit</button>
                            <button id="confirmPaymentBtn" class="btn btn-success">Confirm & Pay</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        function updateDelivery() {

            const deliveryMethod =
                document.getElementById("delivery_method").value;

            const estimatedCostField =
                document.getElementById("estimated_cost");

            const estimatedDeliveryField =
                document.getElementById("estimated_delivery");

            const estimatedDeliveryDateField =
                document.getElementById("estimated_delivery_date");
            const subtotalField = document.getElementById("subtotal");
            const total = document.getElementById("total");

            const taxField = document.getElementById("tax");

            const pricePerKg = {{ $pricePerKg }};
            const insurancePercent = 0.01;
            const taxPercent = {{ $taxPercent }};

            const amountInputs =
                document.querySelectorAll('[name="package_amount[]"]');

            const weightInputs =
                document.querySelectorAll('[name="package_weight[]"]');

            const lengthInputs =
                document.querySelectorAll('[name="package_length[]"]');

            const widthInputs =
                document.querySelectorAll('[name="package_width[]"]');

            const heightInputs =
                document.querySelectorAll('[name="package_height[]"]');

            const volInputs =
                document.querySelectorAll('.vol-weight');

            let subtotal = 0;

            weightInputs.forEach((input, i) => {

                const qty =
                    parseFloat(amountInputs[i].value) || 1;

                const w =
                    parseFloat(weightInputs[i].value) || 0;

                const l =
                    parseFloat(lengthInputs[i].value) || 0;

                const wd =
                    parseFloat(widthInputs[i].value) || 0;

                const h =
                    parseFloat(heightInputs[i].value) || 0;

                const volWeight =
                    (l * wd * h) / 5000;

                if (volInputs[i]) {

                    volInputs[i].value =
                        volWeight.toFixed(2);

                }

                const chargeableWeight =
                    Math.max(w, volWeight);

                const packageCost =
                    chargeableWeight * pricePerKg * qty;

                subtotal += packageCost;

            });

            const insurance = {{ $insurance }};

            const tax =
                subtotal * taxPercent;

            let totalAmount =
                subtotal + insurance + tax;

            if (subtotal <= 0 || !deliveryMethod) {

                estimatedCostField.value = '';
                estimatedDeliveryField.value = '';
                estimatedDeliveryDateField.value = '';

                return;

            }

            const multiplier =
                deliveryMethod === "express" ? 1.5 : 1;

            totalAmount =
                totalAmount * multiplier;

            estimatedCostField.value =
                totalAmount.toFixed(2);
            subtotalField.innerHTML = subtotal.toFixed(2);
            taxField.innerHTML = tax.toFixed(2);
            total.innerHTML =
                totalAmount.toFixed(2);


            const d = new Date();

            const days =
                deliveryMethod === "express" ? 2 : 5;

            d.setDate(d.getDate() + days);

            const day =
                ("0" + d.getDate()).slice(-2);

            const mon =
                ("0" + (d.getMonth() + 1)).slice(-2);

            const year =
                d.getFullYear();

            estimatedDeliveryField.value =
                `${day}-${mon}-${year} (${days} Days)`;

            estimatedDeliveryDateField.value =
                `${year}-${mon}-${day}`;

        }


        document.getElementById("delivery_method")
            .addEventListener('change', updateDelivery);


        document.addEventListener('input', function (e) {

            if (e.target.name &&
                e.target.name.match(/package_(amount|weight|length|width|height)\[\]/)) {

                updateDelivery();

            }

        });


        const container =
            document.getElementById('package-list');

        document.getElementById('add-package')
            .addEventListener('click', function () {

                const firstRow =
                    container.querySelector('.package-row');

                const clone =
                    firstRow.cloneNode(true);

                clone.querySelectorAll('input')
                clone.querySelectorAll('input').forEach(input => {

                    if (input.type != 'button')
                        input.value = '';

                    input.classList.remove('is-invalid');

                });
                container.appendChild(clone);

                updateDelivery();

            });


        document.addEventListener('click', function (e) {

            if (e.target.classList.contains('remove-package')) {

                const rows =
                    document.querySelectorAll('.package-row');

                if (rows.length > 1) {

                    e.target.closest('.package-row').remove();

                    updateDelivery();

                }

            }

        });


        window.addEventListener("load", updateDelivery);

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


    <script>


        document.addEventListener('DOMContentLoaded', function () {

            const receiverName =
                document.getElementById('receiver_name');

            const receiverPhone =
                document.getElementById('receiver_phone');

            const receiverAddress =
                document.getElementById('receiver_address');


            const oldRecipient =
                receiverName.getAttribute('data-old-recipient');

            const oldAddress =
                receiverName.getAttribute('data-old-address');


            function loadRecipient(recipientId, addressId = null) {

                receiverPhone.value = '';

                receiverAddress.innerHTML =
                    '<option value="">Select Address</option>';

                if (!recipientId) return;

                $.ajax({

                    url: '/{{ $role }}/get-recipient-details/' + recipientId,
                    type: 'GET',

                    success: function (data) {

                        receiverPhone.value =
                            data.phone ?? '';

                        if (data.addresses) {

                            data.addresses.forEach(address => {

                                let option =
                                    document.createElement('option');

                                option.value = address.id;

                                option.text =
                                    address.address + ' - ' +
                                    address.city + ', ' +
                                    address.state;


                                if (addressId && addressId == address.id) {

                                    option.selected = true;

                                }

                                receiverAddress.appendChild(option);

                            });


                            if (addressId) {

                                receiverAddress.value =
                                    addressId;

                            }

                        }

                    }

                });

            }


            receiverName.addEventListener(
                'change',
                function () {

                    loadRecipient(this.value);

                });


            if (oldRecipient) {

                loadRecipient(
                    oldRecipient,
                    oldAddress
                );

            }

        });
    </script>


    {{--
    <script>
        document.getElementById('shipmentForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            try {
                const res = await fetch('/api/{{ $role }}/createShipment', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer {{ session('api_token') }}'
                                                    },
                    body: formData
                });

                const data = await res.json();

                if (!res.ok) {
                    if (data.errors) {
                        data.errors.forEach(err => {
                            handleError(err.field, err.message);
                        });
                    }
                    return;
                }

                if (data.payment_url) {
                    window.location.href = data.payment_url;
                }

            } catch (error) {
                alert("Something went wrong");
            }
        });

        function handleError(field, message) {
            let input = document.querySelector(`[name="${field}"]`);

            if (!input) {
                input = document.querySelector(`[name="${field}[]"]`);
            }

            if (!input) return;

            input.classList.add('is-invalid');

            let oldError = input.nextElementSibling;
            if (oldError && oldError.classList.contains('invalid-feedback')) {
                oldError.remove();
            }

            let error = document.createElement('div');
            error.classList.add('invalid-feedback', 'd-block');
            error.innerText = message;

            input.insertAdjacentElement('afterend', error);
        }


    </script> --}}


    <script>

        document.getElementById('shipmentForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            try {
                const res = await fetch('/api/{{ $role }}/validateShipment', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer {{ session('api_token') }}'
                    },
                    body: formData
                });

                const data = await res.json();

                if (!res.ok) {
                    data.errors.forEach(err => handleError(err.field, err.message));
                    return;
                }

                showPreviewModal(data.preview);

            } catch (err) {
                alert("Something went wrong");
            }
        });

        function handleError(field, message) {
            let input = document.querySelector(`[name="${field}"]`);

            if (!input) {
                input = document.querySelector(`[name="${field}[]"]`);
            }

            if (!input) return;

            input.classList.add('is-invalid');

            let oldError = input.nextElementSibling;
            if (oldError && oldError.classList.contains('invalid-feedback')) {
                oldError.remove();
            }

            let error = document.createElement('div');
            error.classList.add('invalid-feedback', 'd-block');
            error.innerText = message;

            input.insertAdjacentElement('afterend', error);
        }


    </script>

    <script>
        document.getElementById('confirmPaymentBtn').addEventListener('click', async function () {

            this.disabled = true;

            let formData = new FormData(document.getElementById('shipmentForm'));

            const res = await fetch('/api/{{ $role }}/createShipment', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer {{ session('api_token') }}'
                },
                body: formData
            });

            const data = await res.json();

            if (data.payment_url) {
                window.location.href = data.payment_url;
            } else {
                this.disabled = false;
            }
        });
    </script>
@endsection