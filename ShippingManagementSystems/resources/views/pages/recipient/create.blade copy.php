@php
    $role = Auth::user()->role;
    $oldAddresses = old('addresses', []);
@endphp

@extends("layouts.{$role}")

@section('content')
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add Recipient</h3>
            </div>

            <form action="{{ route("{$role}.recipient.store") }}" method="POST">
                @csrf
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="receiver_name">Full Name</label>
                            <input type="text" name="receiver_name"
                                class="form-control @error('receiver_name') is-invalid @enderror" placeholder="Full Name"
                                value="{{ old('receiver_name') }}">
                            @error('receiver_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="receiver_phone">Phone</label>
                            <input type="text" name="receiver_phone"
                                class="form-control @error('receiver_phone') is-invalid @enderror" placeholder="Phone"
                                value="{{ old('receiver_phone') }}">
                            @error('receiver_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5>Addresses</h5>
                    <div id="addresses-container">

                        @if(count($oldAddresses) > 0)
                            @foreach($oldAddresses as $i => $address)
                                <div class="address-row mb-3">

                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <input type="text" name="addresses[0][address]"
                                                class="form-control @error('addresses.0.address') is-invalid @enderror"
                                                placeholder="Address">

                                            @error('addresses.0.address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <input type="text" name="addresses[0][country]"
                                                class="form-control @error('addresses.0.country') is-invalid @enderror"
                                                placeholder="Country">

                                            @error('addresses.0.country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-2">

                                        <div class="col-md-4">
                                            <input type="text" name="addresses[0][city]"
                                                class="form-control @error('addresses.0.city') is-invalid @enderror"
                                                placeholder="City">

                                            @error('addresses.0.city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <input type="text" name="addresses[0][state]"
                                                class="form-control @error('addresses.0.state') is-invalid @enderror"
                                                placeholder="State">

                                            @error('addresses.0.state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <input type="text" name="addresses[0][zip_code]"
                                                class="form-control @error('addresses.0.zip_code') is-invalid @enderror"
                                                placeholder="Zip Code">

                                            @error('addresses.0.zip_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-danger remove-address">
                                                Remove
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        @else
                            <div class="address-row mb-3">
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" name="addresses[0][address]"
                                            class="form-control @error('addresses.0.address') is-invalid @enderror"
                                            placeholder="Address">
                                        @error('addresses.0.address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="addresses[0][country]"
                                            class="form-control @error('addresses.0.country') is-invalid @enderror"
                                            placeholder="Country">
                                        @error('addresses.0.country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <input type="text" name="addresses[0][city]"
                                            class="form-control @error('addresses.0.city') is-invalid @enderror"
                                            placeholder="City">
                                        @error('addresses.0.city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="addresses[0][state]"
                                            class="form-control @error('addresses.0.state') is-invalid @enderror"
                                            placeholder="State">
                                        @error('addresses.0.state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 d-flex">
                                        <input type="text" name="addresses[0][zip_code]"
                                            class="form-control me-2 @error('addresses.0.zip_code') is-invalid @enderror"
                                            placeholder="Zip Code">
                                        @error('addresses.0.zip_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        {{-- <button type="button" class="btn btn-danger remove-address">Remove</button> --}}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-danger remove-address">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                            </div>
                        @endif

                    </div>

                    <button type="button" class="btn btn-success mt-2" id="add-address">+ Add another Address</button>
                </div>

                {{-- Submit --}}
                <div class="card-footer mt-3">
                    <button type="submit" class="btn btn-primary">Add Recipient</button>
                    <a href="{{ route("{$role}.recipient.index") }}" class="btn btn-secondary">Return to Dashboard</a>
                </div>
            </form>
        </div>
    </div>

    {{-- JS for dynamic addresses --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let addressIndex = {{ count($oldAddresses) > 0 ? count($oldAddresses) : 1 }};
            const container = document.getElementById('addresses-container');
            const addBtn = document.getElementById('add-address');

            addBtn.addEventListener('click', function () {
                const newRow = document.createElement('div');
                newRow.classList.add('address-row', 'mb-3');
                newRow.innerHTML = `

    <div class="row mb-2">

        <div class="col-md-6">
            <input type="text" data-field="address"
            class="form-control"
            placeholder="Address">
        </div>

        <div class="col-md-6">
            <input type="text" data-field="country"
            class="form-control"
            placeholder="Country">
        </div>

    </div>

    <div class="row mb-2">

        <div class="col-md-4">
            <input type="text"
            data-field="city"
            class="form-control"
            placeholder="City">
        </div>

        <div class="col-md-4">
            <input type="text"
            data-field="state"
            class="form-control"
            placeholder="State">
        </div>

        <div class="col-md-4">
            <input type="text"
            data-field="zip_code"
            class="form-control"
            placeholder="Zip Code">
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <button type="button"
            class="btn btn-danger remove-address">
            Remove
            </button>
        </div>
    </div>

    `;
                container.appendChild(newRow);
                updateIndexes();
            });

            container.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-address')) {
                    e.target.closest('.address-row').remove();
                    updateIndexes();
                }
            });

            function updateIndexes() {
                const rows = container.querySelectorAll('.address-row');
                rows.forEach((row, i) => {
                    row.querySelectorAll('input').forEach(input => {
                        const field = input.getAttribute('data-field');
                        input.name = `addresses[${i}][${field}]`;
                    });
                });
                addressIndex = rows.length;
            }
        });
    </script>
@endsection