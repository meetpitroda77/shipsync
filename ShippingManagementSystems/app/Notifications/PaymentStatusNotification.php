<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $shipment;
    protected $payment;
    protected $status;
    protected $reason;

    public function __construct($shipment, $payment, $status, $reason = null)
    {
        $this->shipment = $shipment;
        $this->payment = $payment;
        $this->status = strtoupper($status);
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {

        $mail = (new MailMessage)
            ->greeting("Hello {$notifiable->name},");

        if ($this->status === 'SUCCESS') {
            return $mail
                ->subject("✅ Payment Successful - Shipment #{$this->shipment->tracking_id}")
                ->line("Your payment was successfully completed.")
                ->line("Amount: $" . $this->payment->amount)
                ->line("Transaction ID: " . ($this->payment->transaction_id ?? 'N/A'))
                ->line('Thank you for your payment!');
        }

        if ($this->status === 'FAILED') {
            return $mail
                ->subject("❌ Payment Failed - Shipment #{$this->shipment->tracking_id}")
                ->line("Your payment could not be completed.")
                ->line("Amount: $" . $this->payment->amount)
                ->when($this->reason, function ($mail) {
                    return $mail->line("Reason: {$this->reason}");
                });
        }

        return $mail
            ->subject("⏳ Payment Processing - Shipment #{$this->shipment->tracking_id}")
            ->line("Your payment is currently being processed.")
            ->line("Amount: $" . $this->payment->amount)
            ->line('We will notify you once it is completed.');
    }
}