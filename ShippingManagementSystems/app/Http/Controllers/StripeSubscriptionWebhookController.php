<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SubscriptionNotification;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StripeSubscriptionWebhookController extends Controller
{
    const STRIPE_STATUS_MAP = [
        'active' => 'active',
        'trialing' => 'trialing',
        'incomplete' => 'incomplete',
        'incomplete_expired' => 'incomplete_expired',
        'past_due' => 'past_due',
        'canceled' => 'cancelled',
        'cancelled' => 'cancelled',
        'unpaid' => 'unpaid',
        'paused' => 'paused',
    ];

    public function handle(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.subscription_webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        Log::info('Subscription webhook received', ['type' => $event->type]);

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event->data->object);
                break;
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'customer.subscription.trial_will_end':
                $this->handleTrialWillEnd($event->data->object);
                break;
            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;
            case 'invoice.payment_action_required':
                $this->handleInvoicePaymentActionRequired($event->data->object);
                break;
            default:
                Log::info('No handler for webhook type', ['type' => $event->type]);
        }

        return response()->json(['status' => 'success']);
    }


    private function handleCheckoutSessionCompleted($session): void
    {
        $user = User::where('stripe_id', $session->customer)->first();
        if (!$user)
            return;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $subscription = \Stripe\Subscription::retrieve([
                'id' => $session->subscription,
                'expand' => ['default_payment_method'],
            ]);

            $user->update([
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'subscription_plan' => $session->metadata->plan ?? 'basic',
                'current_period_end' => $this->getPeriodEnd($subscription),
                'trial_ends_at' => $this->getTrialEndsAt($subscription),
                'subscription_ends_at' => $this->getEndsAt($subscription),
                'pm_type' => $this->getPaymentMethodType($subscription),
                'pm_last_four' => $this->getPaymentMethodLastFour($subscription),
                'shipments_used_this_month' => 0,
                'shipment_counter_reset_at' => now(),
            ]);

            Log::info('checkout.session.completed', [
                'user_id' => $user->id,
                'status' => $subscription->status,
            ]);
        } catch (\Exception $e) {
            Log::error('handleCheckoutSessionCompleted error: ' . $e->getMessage());
        }
    }


    private function handleSubscriptionCreated($subscription): void
    {
        $this->syncSubscription($subscription);

        $user = $this->findUser($subscription);
        if ($user && in_array($subscription->status, ['active', 'trialing'])) {
            $notificationType = $subscription->status === 'trialing' ? 'trialing' : 'created';
            try {
                $user->notify(new SubscriptionNotification($notificationType, $user->subscription_plan));
            } catch (\Exception $e) {
                Log::error('Notification error (created): ' . $e->getMessage());
            }
        }
    }


    private function handleSubscriptionUpdated($subscription): void
    {
        $user = $this->findUser($subscription);
        if (!$user)
            return;
        $oldStatus = $user->subscription_status;
        $oldPlan = $user->subscription_plan;

        $isCancellationScheduled =
            $subscription->cancel_at_period_end === true;

        $this->syncSubscription($subscription);

        $user->refresh();

        $newStatus = $isCancellationScheduled
            ? 'cancelled'
            : $user->subscription_status;
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            Log::info('Subscription was cancelled via webhook', [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            try {
                $user->notify(new SubscriptionNotification('cancelled', $user->subscription_plan));
            } catch (\Exception $e) {
                Log::error('Cancellation notification error: ' . $e->getMessage());
            }
        }


        if ($oldStatus === 'past_due' && $newStatus === 'unpaid') {
            Log::warning('Subscription moved from past_due to unpaid', ['user_id' => $user->id]);
            try {
                $user->notify(new SubscriptionNotification('payment_exhausted', $user->subscription_plan));
            } catch (\Exception $e) {
                Log::error('Payment exhausted notification error: ' . $e->getMessage());
            }
        }

        if ($oldStatus === 'incomplete' && $newStatus === 'incomplete_expired') {
            Log::warning('Subscription expired (incomplete_expired)', ['user_id' => $user->id]);
            try {
                $user->notify(new SubscriptionNotification('payment_expired', $user->subscription_plan));
            } catch (\Exception $e) {
                Log::error('Payment expired notification error: ' . $e->getMessage());
            }
        }


        if ($oldPlan !== $user->subscription_plan) {
            try {
                $user->notify(new SubscriptionNotification('plan_changed', $user->subscription_plan, $oldPlan));
            } catch (\Exception $e) {
                Log::error('Plan change notification error: ' . $e->getMessage());
            }
        }


        if ($oldStatus !== 'past_due' && $newStatus === 'past_due') {
            try {
                $user->notify(new SubscriptionNotification('payment_failed', $user->subscription_plan));
            } catch (\Exception $e) {
                Log::error('Payment failed notification error: ' . $e->getMessage());
            }
        }
    }


    private function handleSubscriptionDeleted($subscription): void
    {
        $user = User::where('subscription_id', $subscription->id)->first();
        if (!$user)
            return;

        $endedAt = $subscription->ended_at
            ?? $subscription->canceled_at
            ?? $subscription->current_period_end
            ?? time();

        $user->update([
            'subscription_status' => 'cancelled',
            'subscription_ends_at' => Carbon::createFromTimestamp($endedAt),
            'trial_ends_at' => null,
        ]);

        Log::info('Subscription deleted', ['user_id' => $user->id]);
    }







    private function handleTrialWillEnd($subscription): void
    {
        $user = $this->findUser($subscription);
        if (!$user)
            return;

        $daysLeft = now()->diffInDays(Carbon::createFromTimestamp($subscription->trial_end));

        if ($daysLeft <= 3 && $daysLeft > 0) {
            try {
                $user->notify(new SubscriptionNotification('trial_ending', $user->subscription_plan, null, $daysLeft));
            } catch (\Exception $e) {
                Log::error('Trial ending notification error: ' . $e->getMessage());
            }
        }

        Log::info('Trial will end', [
            'user_id' => $user->id,
            'days_left' => $daysLeft,
            'trial_end' => $subscription->trial_end
        ]);
    }


    private function handleInvoicePaid($invoice): void
    {
        if (!$invoice->subscription)
            return;

        $user = User::where('stripe_id', $invoice->customer)->first();
        if (!$user)
            return;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $subscription = \Stripe\Subscription::retrieve([
                'id' => $invoice->subscription,
                'expand' => ['default_payment_method'],
            ]);

            $wasPastDue = $user->subscription_status === 'past_due';
            $wasUnpaid = $user->subscription_status === 'unpaid';
            $wasPaused = $user->subscription_status === 'paused';
            $wasIncomplete = in_array($user->subscription_status, ['incomplete', 'incomplete_expired']);

            $newStatus = $subscription->cancel_at_period_end ? 'cancelled' : 'active';

            $updateData = [
                'subscription_status' => $newStatus,
                'current_period_end' => $this->getPeriodEnd($subscription),
                'shipments_used_this_month' => 0,
                'shipment_counter_reset_at' => now(),
            ];

            if ($newStatus === 'active' && $user->trial_ends_at) {
                $updateData['trial_ends_at'] = null;
            }

            if (!$subscription->cancel_at_period_end) {
                $updateData['subscription_ends_at'] = null;
            }

            $pmType = $this->getPaymentMethodType($subscription);
            $pmLastFour = $this->getPaymentMethodLastFour($subscription);
            if ($pmType !== null)
                $updateData['pm_type'] = $pmType;
            if ($pmLastFour !== null)
                $updateData['pm_last_four'] = $pmLastFour;

            $user->update($updateData);

            if ($wasPastDue || $wasUnpaid || $wasPaused) {
                try {
                    $user->notify(new SubscriptionNotification('payment_recovered', $user->subscription_plan));
                } catch (\Exception $e) {
                    Log::error('Payment recovered notification error: ' . $e->getMessage());
                }
            }

            if ($wasIncomplete) {
                try {
                    $user->notify(new SubscriptionNotification('payment_confirmed', $user->subscription_plan));
                } catch (\Exception $e) {
                    Log::error('Payment confirmed notification error: ' . $e->getMessage());
                }
            }

            Log::info('invoice.paid processed', [
                'user_id' => $user->id,
                'old_status' => $user->getOriginal('subscription_status'),
                'new_status' => $newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('handleInvoicePaid error: ' . $e->getMessage());
        }
    }



    private function handleInvoicePaymentFailed($invoice): void
    {
        if (!$invoice->subscription)
            return;

        $user = User::where('stripe_id', $invoice->customer)->first();
        if (!$user)
            return;

        $user->update(['subscription_status' => 'past_due']);

        try {
            $user->notify(new SubscriptionNotification(
                'payment_failed',
                $user->subscription_plan,
                null,
                $invoice->attempt_count ?? 1
            ));
        } catch (\Exception $e) {
            Log::error('Notification error (payment_failed): ' . $e->getMessage());
        }

        Log::info('invoice.payment_failed', [
            'user_id' => $user->id,
            'attempt_count' => $invoice->attempt_count ?? 1,
        ]);
    }


    private function handleInvoicePaymentActionRequired($invoice): void
    {
        if (!$invoice->subscription)
            return;

        $user = User::where('stripe_id', $invoice->customer)->first();
        if (!$user)
            return;

        $user->update(['subscription_status' => 'incomplete']);

        try {
            $user->notify(new SubscriptionNotification('payment_action_required', $user->subscription_plan));
        } catch (\Exception $e) {
            Log::error('Payment action required notification error: ' . $e->getMessage());
        }

        Log::info('invoice.payment_action_required', ['user_id' => $user->id]);
    }


    private function syncSubscription($subscription): void
    {
        $user = $this->findUser($subscription);
        if (!$user)
            return;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $subscription = \Stripe\Subscription::retrieve([
                'id' => $subscription->id,
                'expand' => ['default_payment_method'],
            ]);
            Log::info('Stripe Subscription Update', [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
                'canceled_at' => $subscription->canceled_at,
            ]);
        } catch (\Exception $e) {
            Log::error('syncSubscription retrieve error: ' . $e->getMessage());
            return;
        }

        $stripeStatus = $subscription->status;
        $mappedStatus = self::STRIPE_STATUS_MAP[$stripeStatus] ?? 'inactive';

        if (
            $subscription->cancel_at_period_end &&
            in_array($stripeStatus, ['active', 'trialing'])
        ) {
            $mappedStatus = 'cancelled';
        }

        $newPlan = $this->getPlanFromPriceId($subscription->items->data[0]->price->id);
        $oldPlan = $user->subscription_plan;

        $updateData = [
            'subscription_id' => $subscription->id,
            'subscription_status' => $mappedStatus,
            'subscription_plan' => $newPlan,
            'current_period_end' => $this->getPeriodEnd($subscription),
            'trial_ends_at' => $this->getTrialEndsAt($subscription),
            'subscription_ends_at' => $this->getEndsAt($subscription),
        ];

        if ($oldPlan !== $newPlan) {
            $updateData['shipments_used_this_month'] = 0;
            $updateData['shipment_counter_reset_at'] = now();
        }

        if ($mappedStatus === 'active' && $user->trial_ends_at) {
            $updateData['trial_ends_at'] = null;
        }

        if ($mappedStatus === 'active' && $user->subscription_ends_at) {
            $updateData['subscription_ends_at'] = null;
        }

        $pmType = $this->getPaymentMethodType($subscription);
        $pmLastFour = $this->getPaymentMethodLastFour($subscription);
        if ($pmType !== null)
            $updateData['pm_type'] = $pmType;
        if ($pmLastFour !== null)
            $updateData['pm_last_four'] = $pmLastFour;

        $user->update($updateData);

        Log::info('Subscription synced', [
            'user_id' => $user->id,
            'stripe_status' => $stripeStatus,
            'mapped_status' => $mappedStatus,
            'cancel_at_period_end' => $subscription->cancel_at_period_end
        ]);
    }


    private function findUser($subscription): ?User
    {
        return User::where('subscription_id', $subscription->id)
            ->orWhere('stripe_id', $subscription->customer)
            ->first();
    }

    private function getPeriodEnd($subscription): ?Carbon
    {
        $ts = $subscription->current_period_end
            ?? ($subscription->items->data[0]->current_period_end ?? null)
            ?? ($subscription->status === 'trialing' ? $subscription->trial_end : null);

        return $ts ? Carbon::createFromTimestamp($ts) : null;
    }

    private function getTrialEndsAt($subscription): ?Carbon
    {
        return ($subscription->status === 'trialing' && $subscription->trial_end)
            ? Carbon::createFromTimestamp($subscription->trial_end)
            : null;
    }

    private function getEndsAt($subscription): ?Carbon
    {
        $periodEnd = $subscription->current_period_end
            ?? ($subscription->items->data[0]->current_period_end ?? null);

        if ($subscription->cancel_at_period_end && $periodEnd) {
            return Carbon::createFromTimestamp($periodEnd);
        }

        if (in_array($subscription->status, ['canceled', 'cancelled', 'unpaid', 'incomplete_expired'])) {
            $ts = $subscription->canceled_at ?? $subscription->ended_at ?? $periodEnd;
            return $ts ? Carbon::createFromTimestamp($ts) : null;
        }

        return null;
    }

    private function getPaymentMethodType($subscription): ?string
    {
        return $subscription->default_payment_method->type ?? null;
    }

    private function getPaymentMethodLastFour($subscription): ?string
    {
        return $subscription->default_payment_method->card->last4 ?? null;
    }

    private function getPlanFromPriceId(string $priceId): string
    {
        if ($priceId === config('subscription.plans.basic.price_id'))
            return 'basic';
        if ($priceId === config('subscription.plans.pro.price_id'))
            return 'pro';
        return 'basic';
    }
}
