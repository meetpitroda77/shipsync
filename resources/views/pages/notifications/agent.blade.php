@extends('layouts.agent')

@section('content')
    <div class="container mt-3">
        <h1>Notifications</h1>
        <ul class="list-group">
            @foreach(auth()->user()->notifications as $notification)
                <li class="list-group-item">
                    <strong>{{ $notification->data['message'] }}</strong>
                    <span class="float-right text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endsection