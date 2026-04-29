@php
    $role = Auth::user()->role;
@endphp
@extends("layouts.{$role}")

@section('content')
    <div class="app-content">
        <div class="container-fluid">

            <div class="modal fade" id="addModal">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <form id="settingForm" action="{{ route('createsetting') }}" novalidate method="POST">
                            @csrf

                            <div class="modal-header">
                                <h5>Add Setting</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label>Key</label>
                                    <input type="text" name="key" class="form-control">
                                    <small class="invalid-feedback key_error"></small>
                                </div>

                                <div class="mb-3">
                                    <label>Value</label>
                                    <input type="text" name="value" class="form-control">
                                    <small class="invalid-feedback value_error"></small>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="container">
                <h3>Settings</h3>

                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    + Add Setting
                </button>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                                <tr>
                                    <td>{{ $setting->key }}</td>
                                    <td>{{ $setting->value }}</td>
                                    <td class="d-flex gap-3">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $setting->id }}">
                                            Edit
                                        </button>

                                        <div class="modal fade" id="editModal{{ $setting->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">

                                                    <form class="updateSettingForm"
                                                        action="{{ route('updatesetting', $setting->id) }}" method="POST"
                                                        novalidate>

                                                        @csrf
                                                        @method('PUT')

                                                        <div class="modal-header">
                                                            <h5>Edit Setting</h5>
                                                            <button class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <div class="modal-body">

                                                            <div class="mb-3">
                                                                <label>Key</label>
                                                                <input type="text" value="{{ $setting->key }}"
                                                                    class="form-control" readonly>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label>Value</label>
                                                                <input type="text" name="value" value="{{ $setting->value }}"
                                                                    class="form-control">
                                                                <small class="invalid-feedback value_error"></small>
                                                            </div>

                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">Update</button>
                                                        </div>

                                                    </form>

                                                </div>
                                            </div>
                                        </div> <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $setting->id }}">
                                            Delete
                                        </button>
                                        @if(in_array($role, ['admin']))

                                            <div class="modal fade" id="deleteModal{{ $setting->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('destroySetting', $setting->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm Delete</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete Key {{ $setting->key }}?
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
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.appConfig = {
            createSettingUrl: "{{ route('createsetting') }}",
            csrfToken: "{{ csrf_token() }}"
        };


    </script>
@endsection