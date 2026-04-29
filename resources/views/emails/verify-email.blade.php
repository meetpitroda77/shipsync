@component('mail::message')
# Hello {{ $user->name }},

Thank you for registering on **ShipSync**!  
Please verify your email address by clicking the button below.

@component('mail::button', ['url' => route('verification.verify', $user->id)])
Verify Email
@endcomponent

If you did not create an account, no further action is required.

Thanks,<br>
**ShipSync Team**
@endcomponent