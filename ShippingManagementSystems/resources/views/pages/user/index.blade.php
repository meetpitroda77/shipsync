@php
    $role = Auth::user()->role;
@endphp
@extends("layouts.{$role}")


@section('content')

    <div class="app-content">
        <div class="container-fluid ">




            <div class="d-flex flex-row justify-content-between mb-3">
                <p>View and manage your users</p> <button class="btn btn-success" data-bs-toggle="modal"
                    data-bs-target="#addModel">Create User</button>

            </div>

            <div class="modal fade" id="addModel" tabindex="-1">

                <div class="modal-dialog">

                    <form id="createUserForm" method="POST" action="{{ route('admin.users.store') }}" novalidate>
                        @csrf
                        @method('POST')

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5 class="modal-title">Create User</h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body">

                                <div class="mb-3">

                                    <label class="form-label">Name</label>

                                    <input type="text" class="form-control" name="name">

                                    <div class="invalid-feedback name_error"></div>

                                </div>

                                <div class="mb-3">

                                    <label class="form-label">Email</label>

                                    <input type="email" class="form-control" name="email">

                                    <div class="invalid-feedback email_error"></div>

                                </div>

                                <div class="mb-3">

                                    <label class="form-label">Password</label>

                                    <input type="password" class="form-control" name="password">

                                    <div class="invalid-feedback password_error"></div>

                                </div>

                                <div class="mb-3">

                                    <label class="form-label">Role</label>

                                    <select name="role" class="form-select">

                                        <option value="">Select</option>
                                        <option value="customer">Customer</option>
                                        <option value="admin">Admin</option>
                                        <option value="staff">Staff</option>
                                        <option value="agent">Agent</option>

                                    </select>

                                    <div class="invalid-feedback role_error"></div>

                                </div>

                            </div>

                            <div class="modal-footer">

                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                                    Cancel

                                </button>

                                <button type="submit" class="btn btn-primary">

                                    Create User

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

            <form method="GET" action="{{ route("{$role}.users.index") }}" class="mb-3 d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search "
                    value="{{ request('search') }}">

                <button class="btn btn-primary">Search</button>
            </form>


            <form method="GET" action="{{ route("{$role}.users.index") }}" >
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">

  

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Role</label>
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="customer" {{ $roleuser == 'customer' ? 'selected' : '' }}>
                                        Customer
                                    </option>
                                    <option value="admin" {{ $roleuser == 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>
                                    <option value="staff" {{ $roleuser == 'staff' ? 'selected' : '' }}>
                                        Staff
                                    </option>
                                    <option value="agent" {{ $roleuser == 'agent' ? 'selected' : '' }}>
                                        Agent
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

                                <a href="{{ route("{$role}.users.index") }}" class="btn btn-secondary w-100">
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
                            <a href="{{ route("{$role}.users.index", [
        'sort_field' => 'id',
        'sort_direction' => $sortField == 'id' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                ID
                            </a>
                        </th>

                        <th>
                            <a href="{{ route("{$role}.users.index", [
        'sort_field' => 'name',
        'sort_direction' => $sortField == 'name' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                Name
                            </a>
                        </th>

                        <th>
                            <a href="{{ route("{$role}.users.index", [
        'sort_field' => 'email',
        'sort_direction' => $sortField == 'email' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                Email
                            </a>
                        </th>

                        <th>
                            <a href="{{ route("{$role}.users.index", [
        'sort_field' => 'role',
        'sort_direction' => $sortField == 'role' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                Role
                            </a>
                        </th>
                        <th>
                            <a href="{{ route("{$role}.users.index", [
        'sort_field' => 'created_at',
        'sort_direction' => $sortField == 'created_at' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                Created At
                            </a>
                        </th>

                        <th>
                            <a href="{{ route("{$role}.users.index", [
        'sort_field' => 'email_verified_at',
        'sort_direction' => $sortField == 'email_verified_at' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                Email Verified At
                            </a>
                        </th>

                        <th>
                            Actions
                        </th>


                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>

                            <td>{{ $user->email }}</td>
                            <td>
                                {{-- {{ $user->role }} --}}


                                <select class="form-select role-select" data-id="{{ $user->id }}">
                                    <option value="admin" @selected($user->role == 'admin')>Admin</option>
                                    <option value="staff" @selected($user->role == 'staff')>Staff</option>
                                    <option value="customer" @selected($user->role == 'customer')>Customer</option>
                                    <option value="agent" @selected($user->role == 'agent')>Agent</option>
                                </select>
                            </td>
                            <td>{{ $user->created_at }}</td>
                            <td>
                                {{ $user->email_verified_at ? $user->email_verified_at : 'Not Verified' }}
                            </td>

                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal{{ $user->id }}">
                                    Delete
                                </button>
                                @if(in_array($role, ['admin']))

                                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route("{$role}.destroyUser", $user->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete {{ $user->name }}?
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
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No Users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            <div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>


@endsection