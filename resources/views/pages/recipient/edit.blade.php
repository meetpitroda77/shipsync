@php
    $role = Auth::user()->role;
    $oldAddresses = old(
        'addresses',
        $recipient->addresses->toArray()
    );
@endphp

@extends("layouts.{$role}")

@section('content')

    <div class="container-fluid">

        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">Edit Recipient</h3>
            </div>

            <form action="{{ route(
        "{$role}.recipient.update",
        $recipient->id
    ) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="card-body">


                    <div class="row mb-3">

                        <div class="col-md-6">

                            <label>Full Name</label>

                            <input type="text" name="receiver_name" class="form-control
    @error('receiver_name') is-invalid @enderror" value="{{ old(
        'receiver_name',
        $recipient->receiver_name
    ) }}">

                            @error('receiver_name')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>

                        <div class="col-md-6">

                            <label>Phone</label>

                            <input type="text" name="receiver_phone" class="form-control
    @error('receiver_phone') is-invalid @enderror" value="{{ old(
        'receiver_phone',
        $recipient->receiver_phone
    ) }}">

                            @error('receiver_phone')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>

                    </div>

                    <h5>Addresses</h5>

                    <div id="addresses-container">

                        @foreach($oldAddresses as $i => $address)

                                                <div class="address-row mb-3">
                                                  <input type="hidden" data-field="id" name="addresses[{{$i}}][id]" value="{{ $address['id'] ?? '' }}">

                                                    <div class="row mb-2">

                                                        <div class="col-md-6">

                                                            <input type="text" name="addresses[{{$i}}][address]" data-field="address" class="form-control
                            @error("addresses.$i.address")
                                is-invalid
                            @enderror" placeholder="Address" value="{{ old(
                                    "addresses.$i.address",
                                    $address['address'] ?? ''
                                ) }}">

                                                            @error("addresses.$i.address")

                                                                <div class="invalid-feedback">

                                                                    {{ $message }}

                                                                </div>

                                                            @enderror

                                                        </div>

                                                        <div class="col-md-6">

                                                            <input type="text" name="addresses[{{$i}}][country]" data-field="country" class="form-control
                            @error("addresses.$i.country")
                                is-invalid
                            @enderror" placeholder="Country" value="{{ old(
                                    "addresses.$i.country",
                                    $address['country'] ?? ''
                                ) }}">

                                                            @error("addresses.$i.country")

                                                                <div class="invalid-feedback">

                                                                    {{ $message }}

                                                                </div>

                                                            @enderror

                                                        </div>

                                                    </div>

                                                    <div class="row mb-2">

                                                        <div class="col-md-4">

                                                            <input type="text" name="addresses[{{$i}}][city]" data-field="city" class="form-control
                            @error("addresses.$i.city")
                                is-invalid
                            @enderror" placeholder="City" value="{{ old(
                                    "addresses.$i.city",
                                    $address['city'] ?? ''
                                ) }}">

                                                            @error("addresses.$i.city")

                                                                <div class="invalid-feedback">

                                                                    {{ $message }}

                                                                </div>

                                                            @enderror

                                                        </div>

                                                        <div class="col-md-4">

                                                            <input type="text" name="addresses[{{$i}}][state]" data-field="state" class="form-control
                            @error("addresses.$i.state")
                                is-invalid
                            @enderror" placeholder="State" value="{{ old(
                                    "addresses.$i.state",
                                    $address['state'] ?? ''
                                ) }}">

                                                            @error("addresses.$i.state")

                                                                <div class="invalid-feedback">

                                                                    {{ $message }}

                                                                </div>

                                                            @enderror

                                                        </div>

                                                        <div class="col-md-4">

                                                            <input type="text" name="addresses[{{$i}}][zip_code]" data-field="zip_code" class="form-control
                            @error("addresses.$i.zip_code")
                                is-invalid
                            @enderror" placeholder="Zip Code" value="{{ old(
                                    "addresses.$i.zip_code",
                                    $address['zip_code'] ?? ''
                                ) }}">

                                                            @error("addresses.$i.zip_code")

                                                                <div class="invalid-feedback">

                                                                    {{ $message }}

                                                                </div>

                                                            @enderror

                                                        </div>

                                                    </div>

                                                    <div class="text-end">

                                                        <button type="button" class="btn btn-danger remove-address">

                                                            Remove

                                                        </button>

                                                    </div>

                                                </div>

                        @endforeach

                    </div>

                    <button type="button" class="btn btn-success mt-2" id="add-address">

                        + Add Address

                    </button>

                </div>

                <div class="card-footer">

                    <button type="submit" class="btn btn-primary">

                        Update Recipient

                    </button>

                    <a href="{{ route("{$role}.recipient.index") }}" class="btn btn-secondary">

                        Back

                    </a>

                </div>

            </form>

        </div>

    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const container =
                document.getElementById('addresses-container');

            const addBtn =
                document.getElementById('add-address');

            function updateIndexes() {

                const rows =
                    container.querySelectorAll('.address-row');

                rows.forEach((row, i) => {

                    row.querySelectorAll('input')
                        .forEach(input => {

                            const field =
                                input.getAttribute('data-field');

                            input.name =
                                `addresses[${i}][${field}]`;

                        });

                });

            }

            addBtn.addEventListener('click', function () {

                const firstRow =
                    container.querySelector('.address-row');

                const newRow =
                    firstRow.cloneNode(true);

                newRow.querySelectorAll('input')
                    .forEach(input => input.value = '');

                container.appendChild(newRow);

                updateIndexes();

            });

            container.addEventListener('click',
                function (e) {

                    if (e.target.classList
                        .contains('remove-address')) {

                        const rows =
                            container.querySelectorAll('.address-row');

                        if (rows.length > 1) {

                            e.target.closest('.address-row')
                                .remove();

                            updateIndexes();

                        }

                    }

                });

            updateIndexes();

        });

    </script>

@endsection