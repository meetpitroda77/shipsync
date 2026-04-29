@component('mail::message')

# Shipment #{{ $tracking_id }} Status Update

Dear {{ $receiver_name }},

The status of your shipment has been updated to **{{ $status }}**.

To view the details of the shipment, please click the button below:

@component('mail::button', ['url' => $link])
View Shipment Details
@endcomponent

Thank you for using our service.

Regards,  
The Shipping Team

@endcomponent