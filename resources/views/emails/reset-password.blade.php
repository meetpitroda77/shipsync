@component('mail::message')
# Reset Your Password

Click the button below to reset your password:

@component('mail::button', ['url' => url('/reset-password/'.$token).'?email='.$email])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Regards,  
The Shipping Team
@endcomponent