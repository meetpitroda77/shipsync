<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $event,
        protected ?string $plan,
        protected ?string $oldPlan = null,
        protected ?int $attemptCount = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $planName    = $this->planLabel($this->plan);
        $oldPlanName = $this->planLabel($this->oldPlan);

        [$subject, $body] = $this->copy($planName, $oldPlanName);

        $actionText     = 'Manage Subscription';
        $additionalLines = [];

        if ($this->event === 'payment_failed') {
            $additionalLines[] = 'Please update your payment method to avoid service interruption.';
            $actionText = 'Update Payment Method';
        }

        if ($this->event === 'payment_exhausted') {
            $additionalLines[] = 'All payment attempts have failed. Your subscription has been cancelled.';
            $additionalLines[] = 'You can resubscribe at any time to restore access.';
            $actionText = 'Resubscribe Now';
        }

        if ($this->event === 'paused') {
            $additionalLines[] = 'Your subscription is paused because no payment method is on file.';
            $additionalLines[] = 'Please add a payment method to resume service.';
            $actionText = 'Add Payment Method';
        }

        if ($this->event === 'trial_ending') {
            $additionalLines[] = "Your free trial ends in {$this->attemptCount} days.";
            $additionalLines[] = 'Add a payment method now to continue uninterrupted service.';
            $actionText = 'Add Payment Method';
        }

        if ($this->event === 'payment_action_required') {
            $additionalLines[] = 'Your card was saved but the initial charge could not be processed.';
            $additionalLines[] = 'Please update your payment method or complete authentication to activate your subscription.';
            $actionText = 'Update Payment Method';
        }

        if ($this->event === 'checkout_payment_failed') {
            $additionalLines[] = 'Your card was saved but the initial charge failed.';
            $additionalLines[] = 'Please update your payment method or use a different card.';
            $actionText = 'Update Payment Method';
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line($body);

        foreach ($additionalLines as $line) {
            $mail->line($line);
        }

        return $mail->action($actionText, $this->getDashboardUrl())
            ->line('Thank you for using our service!');
    }

    private function copy(string $planName, ?string $oldPlanName): array
    {
        return match ($this->event) {
            'created' => [
                "Welcome to {$planName}!",
                "Your {$planName} subscription has been successfully activated.",
            ],
            'trialing' => [
                "Your {$planName} Trial Has Started!",
                "Your free trial of {$planName} is now active. Your first payment will be collected when the trial ends.",
            ],
            'trial_ending' => [
                "Your {$planName} Trial Ends Soon!",
                "Your free trial ends in {$this->attemptCount} days. Add a payment method to continue service.",
            ],
            'payment_confirmed' => [
                "Payment Confirmed!",
                "Your payment has been successfully processed. Your {$planName} subscription is now active.",
            ],
            'payment_exhausted' => [
                "Subscription Cancelled - Payment Failed",
                "All payment attempts have failed. Your {$planName} subscription has been cancelled.",
            ],
            'payment_expired' => [
                "Subscription Expired",
                "Your subscription payment was not completed within 23 hours and has expired.",
            ],
            'paused' => [
                "Subscription Paused",
                "Your {$planName} subscription is paused because no payment method is on file.",
            ],
            'resumed' => [
                "Subscription Resumed",
                "Your {$planName} subscription has been resumed. Thank you for updating your payment method!",
            ],
            'plan_changed' => [
                "Plan Changed: {$planName}",
                "Your subscription has been changed from {$oldPlanName} to {$planName}.",
            ],
            'upgraded' => [
                "Subscription Upgraded to {$planName}!",
                "Your subscription has been upgraded from {$oldPlanName} to {$planName}. Thank you for upgrading!",
            ],
            'updated' => [
                "Subscription Updated",
                "Your subscription has been updated successfully.",
            ],
            'cancelled' => [
                "Subscription Cancellation Scheduled",
                "Your {$planName} subscription has been cancelled. You will keep full access until the end of your current billing period.",
            ],
            'reactivated' => [
                "Subscription Reactivated",
                "Your {$planName} subscription has been reactivated successfully. Your normal billing schedule will continue.",
            ],
            'payment_failed' => [
                "Payment Failed — Action Required",
                "We were unable to process your payment for {$planName}"
                    . ($this->attemptCount ? " (attempt {$this->attemptCount})" : "")
                    . ". Please update your payment method to avoid service interruption.",
            ],
            'payment_action_required' => [
                "Action Required — Payment Could Not Be Processed",
                "Your card was saved but we were unable to charge it for your {$planName} subscription. Please update your payment method to activate your subscription.",
            ],
            'checkout_payment_failed' => [
                "Payment Failed — Card Declined",
                "Your card was attached but we could not charge it for your {$planName} subscription. Please use a different card.",
            ],
            'payment_recovered' => [
                "Payment Recovered — {$planName}",
                "Great news! Your recent payment has been processed successfully. Your {$planName} subscription is now active again.",
            ],
            default => [
                "Subscription Update",
                "Your subscription has been updated.",
            ],
        };
    }

    private function planLabel(?string $plan): string
    {
        return match ($plan) {
            'pro'   => 'Pro Plan',
            'basic' => 'Basic Plan',
            default => 'your plan',
        };
    }

    private function getDashboardUrl(): string
    {
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');
        return $frontendUrl . '/customer/subscription/dashboard';
    }
}
