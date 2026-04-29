@php
    $role = Auth::user()->role;
@endphp
@extends("layouts.{$role}")


@section('content')

    <div class="app-content">
        <div class="container-fluid ">




            <div class="d-flex flex-row justify-content-between mb-3">

                <h3>Recipients</h3>
                <a href="{{ route("{$role}.recipient.recipientform") }}" class="btn btn-success">Create Recipient</a>

            </div>



            <form method="GET" action="{{ route("{$role}.recipient.index") }}" class="mb-3 d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search "
                    value="{{ request('search') }}">

                <button class="btn btn-primary">Search</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route("{$role}.recipient.index", [
        'sort_field' => 'id',
        'sort_direction' => $sortField == 'id' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    ID
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.recipient.index", [
        'sort_field' => 'receiver_name',
        'sort_direction' => $sortField == 'receiver_name' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Name
                                </a>
                            </th>

                            <th>
                                <a href="{{ route("{$role}.recipient.index", [
        'sort_field' => 'receiver_phone',
        'sort_direction' => $sortField == 'receiver_phone' && $sortDirection == 'asc' ? 'desc' : 'asc',
        'search' => request('search')
    ]) }}">
                                    Phone
                                </a>
                            </th>


                            <th>
                                Actions
                            </th>


                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recipients as $recipient)
                            <tr>
                                <td>{{ $recipient->id }}</td>
                                <td>{{ $recipient->receiver_name }}</td>
                                <td>{{ $recipient->receiver_phone }}</td>



                                <td class="d-flex gap-2">
                                    @if(in_array($role, ['admin', 'customer']))
                                        <a href="{{ route("{$role}.recipient.edit", $recipient->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $recipient->id }}">
                                            Delete
                                        </button>

                                        <div class="modal fade" id="deleteModal{{ $recipient->id }}" tabindex="-1">
                                            <div class="modal-dialog">

                                                <form action="{{ route("{$role}.recipient.destroy", $recipient->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirm Delete</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete {{ $recipient->receiver_name }}?
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
                                <td colspan="6" class="text-center">No Recipients found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            <div>
                {{ $recipients->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>


@endsection