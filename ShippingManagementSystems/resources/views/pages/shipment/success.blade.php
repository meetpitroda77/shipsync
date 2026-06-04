@extends('layouts.customer')

@section('content')
<div class="container">
    <h2>Payment Successful!</h2>
    <p>Your shipment #{{ $shipment->tracking_id }} has been created and paid successfully.</p>
</div>
@endsection