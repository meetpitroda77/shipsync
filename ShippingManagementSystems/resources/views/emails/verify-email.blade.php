@component('mail::message')
# Verify Your Email

Hello {{ $user->name }},

Your account has been created successfully.

## Account Credentials

- Email: {{ $user->email }}
- Password: {{ $password }}

Click below to verify your email address.

@component('mail::button', ['url' => $verificationUrl])
Verify Email
@endcomponent

Thanks,<br>
**ShipSync Team**
@endcomponent