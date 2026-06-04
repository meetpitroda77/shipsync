<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationUrl;
    public $user;
    public $password;

    public function __construct(User $user, $password)
    {
        $this->user = $user;
        $this->password = $password;

        $this->verificationUrl =
            rtrim(env('FRONTEND_URL'), '/') . "/verify-email/{$user->id}";
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
            ->markdown('emails.verify-email');
    }
}
