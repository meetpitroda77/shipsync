@extends('layouts.customer')

@section('content')
<div class="container">
    <h2>Payment Cancelled</h2>
    <p>Your shipment #{{ $shipment->tracking_id }} was not paid. Please try again.</p>
</div>
@endsection