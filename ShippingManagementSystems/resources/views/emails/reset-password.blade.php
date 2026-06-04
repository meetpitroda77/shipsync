@component('mail::message')
# Reset Your Password

Click the button below to reset your password:

@component('mail::button', ['url' => env('FRONTEND_URL') . 'reset-password?token=' . $token . '&email=' . $email])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Regards,
The Shipping Team
@endcomponent