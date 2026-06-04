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

        Log::info('Webhook received', ['type' => $event->type]);

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event->data->object);
                break;
            case 'customer.subscription.created':
                $this->handleCustomerSubscriptionCreated($event->data->object);
                break;
            case 'customer.subscription.updated':
                $this->handleCustomerSubscriptionUpdated($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleCustomerSubscriptionDeleted($event->data->object);
                break;
            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;
            default:
                Log::info('No handler for webhook type', ['type' => $event->type]);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handleCheckoutSessionCompleted($session)
    {
        $user = User::where('stripe_id', $session->customer)->first();
        if (!$user) return;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $subscription = \Stripe\Subscription::retrieve([
                'id'     => $session->subscription,
                'expand' => ['default_payment_method'],
            ]);

            $updateData = [
                'subscription_id'           => $subscription->id,
                'subscription_status'       => $subscription->status,
                'subscription_plan'         => $session->metadata->plan ?? 'basic',
                'current_period_end'        => $this->getPeriodEnd($subscription),
                'trial_ends_at'             => $this->getTrialEndsAt($subscription),
                'subscription_ends_at'      => $this->getEndsAt($subscription),
                'pm_type'                   => $this->getPaymentMethodType($subscription),
                'pm_last_four'              => $this->getPaymentMethodLastFour($subscription),
                'shipments_used_this_month' => 0,
                'shipment_counter_reset_at' => now(),
            ];

            $user->update($updateData);

            Log::info('Checkout completed', [
                'user_id'            => $user->id,
                'status'             => $subscription->status,
                'current_period_end' => $updateData['current_period_end'],
                'trial_ends_at'      => $updateData['trial_ends_at'],
            ]);
        } catch (\Exception $e) {
            Log::error('Checkout session error: ' . $e->getMessage());
        }
    }

    private function handleCustomerSubscriptionCreated($subscription)
    {
        $this->updateUserSubscription($subscription);

        $user = User::where('subscription_id', $subscription->id)
            ->orWhere('stripe_id', $subscription->customer)
            ->first();

        if ($user) {
            $user->notify(
                new SubscriptionNotification(
                    'created',
                    $user->subscription_plan
                )
            );
        }
    }
    private function handleCustomerSubscriptionUpdated($subscription)
    {
        $user = User::where('subscription_id', $subscription->id)
            ->orWhere('stripe_id', $subscription->customer)
            ->first();

        $oldPlan = $user?->subscription_plan;

        $this->updateUserSubscription($subscription);

        $user?->refresh();

        if ($user) {
            $event = $oldPlan !== $user->subscription_plan
                ? 'upgraded'
                : 'updated';

            $user->notify(
                new SubscriptionNotification(
                    $event,
                    $user->subscription_plan,
                    $oldPlan
                )
            );
        }
    }

    private function handleCustomerSubscriptionDeleted($subscription)
    {
        $user = User::where('subscription_id', $subscription->id)->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'cancelled',
                'subscription_ends_at' => Carbon::createFromTimestamp(
                    $subscription->ended_at ??
                        $subscription->canceled_at ??
                        $subscription->current_period_end ??
                        time()
                ),
                'trial_ends_at' => null,
            ]);

            $user->notify(
                new SubscriptionNotification(
                    'cancelled',
                    $user->subscription_plan
                )
            );
        }
    }

    private function handleInvoicePaid($invoice)
    {
        if (!$invoice->subscription) return;

        $user = User::where('stripe_id', $invoice->customer)->first();
        if (!$user) return;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $updateData = [
            'shipments_used_this_month' => 0,
            'shipment_counter_reset_at' => now(),
        ];

        if ($user->subscription_status === 'past_due') {
            $updateData['subscription_status'] = 'active';
        }

        try {
            $subscription = \Stripe\Subscription::retrieve([
                'id'     => $invoice->subscription,
                'expand' => ['default_payment_method'],
            ]);

            $updateData['current_period_end'] = $this->getPeriodEnd($subscription);

            if ($user->trial_ends_at && $subscription->status === 'active') {
                $updateData['trial_ends_at'] = null;
            }

            if (!$subscription->cancel_at_period_end) {
                $updateData['subscription_ends_at'] = null;
            }

            $pmType     = $this->getPaymentMethodType($subscription);
            $pmLastFour = $this->getPaymentMethodLastFour($subscription);

            if ($pmType !== null)     $updateData['pm_type']      = $pmType;
            if ($pmLastFour !== null) $updateData['pm_last_four'] = $pmLastFour;

            $user->update($updateData);
            if ($user->subscription_status === 'past_due') {
                $user->notify(
                    new SubscriptionNotification(
                        'payment_recovered',
                        $user->subscription_plan
                    )
                );
            }

            Log::info('Invoice paid', [
                'user_id'            => $user->id,
                'current_period_end' => $updateData['current_period_end'],
            ]);
        } catch (\Exception $e) {
            Log::error('Invoice paid error: ' . $e->getMessage());
        }
    }

    private function handleInvoicePaymentFailed($invoice)
    {
        if (!$invoice->subscription) return;

        $user = User::where('stripe_id', $invoice->customer)->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'past_due'
            ]);

            $user->notify(
                new SubscriptionNotification(
                    'payment_failed',
                    $user->subscription_plan,
                    null,
                    $invoice->attempt_count ?? 1
                )
            );
        }
    }

    private function updateUserSubscription($subscription)
    {
        $user = User::where('subscription_id', $subscription->id)
            ->orWhere('stripe_id', $subscription->customer)
            ->first();

        if (!$user) return;

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $subscription = \Stripe\Subscription::retrieve([
            'id'     => $subscription->id,
            'expand' => ['default_payment_method'],
        ]);

        $status = $subscription->cancel_at_period_end ? 'cancelled' : $subscription->status;

        $updateData = [
            'subscription_id'      => $subscription->id,
            'subscription_status'  => $status,
            'subscription_plan'    => $this->getPlanFromPriceId($subscription->items->data[0]->price->id),
            'current_period_end'   => $this->getPeriodEnd($subscription),
            'trial_ends_at'        => $this->getTrialEndsAt($subscription),
            'subscription_ends_at' => $this->getEndsAt($subscription),
        ];

        $pmType     = $this->getPaymentMethodType($subscription);
        $pmLastFour = $this->getPaymentMethodLastFour($subscription);

        if ($pmType !== null)     $updateData['pm_type']      = $pmType;
        if ($pmLastFour !== null) $updateData['pm_last_four'] = $pmLastFour;

        $user->update($updateData);
    }

    private function getPeriodEnd($subscription)
    {
        $timestamp = $subscription->current_period_end
            ?? ($subscription->items->data[0]->current_period_end ?? null)
            ?? ($subscription->status === 'trialing' ? $subscription->trial_end : null);

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    private function getTrialEndsAt($subscription)
    {
        if ($subscription->status === 'trialing' && $subscription->trial_end) {
            return Carbon::createFromTimestamp($subscription->trial_end);
        }

        return null;
    }

    private function getEndsAt($subscription)
    {
        $periodEnd = $subscription->current_period_end
            ?? ($subscription->items->data[0]->current_period_end ?? null);

        if ($subscription->cancel_at_period_end && $periodEnd) {
            return Carbon::createFromTimestamp($periodEnd);
        }

        if ($subscription->status === 'canceled') {
            $timestamp = $subscription->canceled_at ?? $subscription->ended_at ?? $periodEnd;
            return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
        }

        return null;
    }

    private function getPaymentMethodType($subscription)
    {
        return $subscription->default_payment_method->type ?? null;
    }

    private function getPaymentMethodLastFour($subscription)
    {
        return $subscription->default_payment_method->card->last4 ?? null;
    }

    private function getPlanFromPriceId(string $priceId): string
    {
        $basicPriceId = config('subscription.plans.basic.price_id');
        $proPriceId   = config('subscription.plans.pro.price_id');

        if ($priceId === $basicPriceId) return 'basic';
        if ($priceId === $proPriceId)   return 'pro';
        return 'basic';
    }
}
