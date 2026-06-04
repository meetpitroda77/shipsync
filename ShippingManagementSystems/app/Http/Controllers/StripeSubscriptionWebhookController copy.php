<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StripeSubscriptionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.subscription_webhook_secret');

        Log::info('Subscription webhook received', [
            'has_secret' => !empty($webhookSecret),
            'has_signature' => !empty($sigHeader),
            'payload_length' => strlen($payload)
        ]);

        if (!$webhookSecret) {
            Log::error('Stripe subscription webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Error parsing webhook: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        Log::info('Webhook event processed', ['type' => $event->type]);

        try {
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

                case 'invoice.paid':
                    $this->handleInvoicePaid($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;

                default:
                    Log::info('Unhandled event type: ' . $event->type);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'success', 'warning' => $e->getMessage()]);
        }

        return response()->json(['status' => 'success']);
    }

   
    private function handleCheckoutSessionCompleted($session)
    {
     

        $user = User::where('stripe_id', $session->customer)->first();


        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            sleep(2);

            $subscription = \Stripe\Subscription::retrieve([
                'id' => $session->subscription,
                'expand' => [
                    'default_payment_method',
                    'latest_invoice.payment_intent',
                    'latest_invoice.lines',
                    'items.data.price.product'
                ]
            ]);

            $currentPeriodEnd = $subscription->current_period_end;
            $subscriptionEndsAt = null;
            $cancelAtPeriodEnd = $subscription->cancel_at_period_end;

            $isTrialing = ($subscription->status === 'trialing' && $subscription->trial_end);

            if ($isTrialing) {
                $currentPeriodEnd = $subscription->trial_end;
              
            }
            elseif (!$currentPeriodEnd && $subscription->latest_invoice) {
                $invoice = $subscription->latest_invoice;

                if ($invoice && $invoice->lines && $invoice->lines->data[0]) {
                    $lineItem = $invoice->lines->data[0];
                    if (isset($lineItem->period)) {
                        $currentPeriodEnd = $lineItem->period->end;
                        Log::info('Got period end from invoice', [
                            'period_end' => $currentPeriodEnd,
                            'period_start' => $lineItem->period->start
                        ]);
                    }
                }
            }

            if ($cancelAtPeriodEnd) {
                $subscriptionEndsAt = $currentPeriodEnd ?: $subscription->current_period_end;
                Log::info('Subscription set to cancel at period end', [
                    'subscription_ends_at' => $subscriptionEndsAt,
                    'formatted' => $subscriptionEndsAt ? date('Y-m-d H:i:s', $subscriptionEndsAt) : null
                ]);
            } elseif ($subscription->status === 'canceled') {
                $subscriptionEndsAt = $subscription->canceled_at ?: $subscription->ended_at ?: $currentPeriodEnd;
                Log::info('Subscription already cancelled', [
                    'subscription_ends_at' => $subscriptionEndsAt,
                    'canceled_at' => $subscription->canceled_at
                ]);
            }

            $pmType = null;
            $pmLastFour = null;

            if ($subscription->default_payment_method) {
                $paymentMethod = $subscription->default_payment_method;
                $pmType = $paymentMethod->type ?? null;
                $pmLastFour = $paymentMethod->card->last4 ?? null;
            }

            if (!$pmType && $session->customer) {
                $customer = \Stripe\Customer::retrieve([
                    'id' => $session->customer,
                    'expand' => ['default_payment_method', 'invoice_settings']
                ]);

                if ($customer->default_payment_method) {
                    $pmType = $customer->default_payment_method->type ?? null;
                    $pmLastFour = $customer->default_payment_method->card->last4 ?? null;
                } elseif ($customer->invoice_settings->default_payment_method) {
                    $paymentMethod = \Stripe\PaymentMethod::retrieve(
                        $customer->invoice_settings->default_payment_method
                    );
                    $pmType = $paymentMethod->type ?? null;
                    $pmLastFour = $paymentMethod->card->last4 ?? null;
                }
            }

            $plan = $session->metadata->plan ?? 'basic';

            $updateData = [
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'subscription_plan' => $plan,
                'current_period_end' => $currentPeriodEnd
                    ? Carbon::createFromTimestamp($currentPeriodEnd)
                    : null,
                'subscription_ends_at' => $subscriptionEndsAt
                    ? Carbon::createFromTimestamp($subscriptionEndsAt)
                    : null,
                'pm_type' => $pmType,
                'pm_last_four' => $pmLastFour,
                'shipments_used_this_month' => 0,
                'shipment_counter_reset_at' => now(),
            ];

            $user->update($updateData);

        } catch (\Exception $e) {
          
            throw $e;
        }
    }

 
    private function handleSubscriptionCreated($subscription)
    {
      

        $user = User::where('stripe_id', $subscription->customer)->first();

        if (!$user) {
            Log::warning('User not found for subscription', ['customer' => $subscription->customer]);
            return;
        }

        $plan = $this->getPlanFromPriceId($subscription->items->data[0]->price->id);

        $subscriptionEndsAt = null;
        if ($subscription->cancel_at_period_end) {
            $subscriptionEndsAt = $subscription->current_period_end;
        } elseif ($subscription->status === 'canceled') {
            $subscriptionEndsAt = $subscription->canceled_at ?: $subscription->ended_at;
        }

        $pmType = null;
        $pmLastFour = null;

        if ($subscription->default_payment_method) {
            $pmType = $subscription->default_payment_method->type ?? null;
            $pmLastFour = $subscription->default_payment_method->card->last4 ?? null;
        }

     
        $updateData = [
            'subscription_id' => $subscription->id,
            'subscription_status' => $subscription->status,
            'subscription_plan' => $plan,
        ];

        if (!$user->current_period_end && $subscription->current_period_end) {
            $updateData['current_period_end'] = Carbon::createFromTimestamp($subscription->current_period_end);
         
        }

        if (!$user->subscription_ends_at && $subscriptionEndsAt) {
            $updateData['subscription_ends_at'] = Carbon::createFromTimestamp($subscriptionEndsAt);
        }

        if (!$user->pm_type && $pmType) {
            $updateData['pm_type'] = $pmType;
            $updateData['pm_last_four'] = $pmLastFour;
           
        }

        $user->update($updateData);

       
    }

   
    private function handleSubscriptionUpdated($subscription)
    {
        Log::info('Processing customer.subscription.updated', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'cancel_at_period_end' => $subscription->cancel_at_period_end,
            'current_period_end' => $subscription->current_period_end
        ]);

        $user = User::where('subscription_id', $subscription->id)->first();

        if (!$user) {
            Log::warning('User not found for subscription update', ['subscription_id' => $subscription->id]);
            return;
        }

        $currentPeriodEnd = $subscription->current_period_end;
        $subscriptionEndsAt = null;
        $status = $subscription->status;

        if ($subscription->cancel_at_period_end) {
            $subscriptionEndsAt = $currentPeriodEnd;
            $status = 'active'; 
            Log::info('Subscription scheduled for cancellation at period end', [
                'ends_at' => $subscriptionEndsAt,
                'ends_at_formatted' => $subscriptionEndsAt ? date('Y-m-d H:i:s', $subscriptionEndsAt) : null
            ]);
        } elseif ($subscription->status === 'canceled') {
            $subscriptionEndsAt = $subscription->canceled_at ?: $subscription->ended_at ?: $currentPeriodEnd;
            $status = 'canceled';
            Log::info('Subscription has been cancelled', [
                'ends_at' => $subscriptionEndsAt,
                'canceled_at' => $subscription->canceled_at
            ]);
        } elseif ($subscription->status === 'past_due') {
            $status = 'past_due';
            $subscriptionEndsAt = $currentPeriodEnd;
            
        } else {
            $subscriptionEndsAt = null;
            Log::info('Subscription is active with no end date');
        }

        $pmType = null;
        $pmLastFour = null;

        if ($subscription->default_payment_method) {
            $pmType = $subscription->default_payment_method->type ?? null;
            $pmLastFour = $subscription->default_payment_method->card->last4 ?? null;
        }

        $updateData = [
            'subscription_status' => $status,
        ];

        if ($currentPeriodEnd) {
            $updateData['current_period_end'] = Carbon::createFromTimestamp($currentPeriodEnd);
        }

        if ($subscriptionEndsAt !== null) {
            $updateData['subscription_ends_at'] = Carbon::createFromTimestamp($subscriptionEndsAt);
        } elseif (!$subscription->cancel_at_period_end && $subscription->status !== 'canceled') {
            $updateData['subscription_ends_at'] = null;
        }

        if ($pmType) {
            $updateData['pm_type'] = $pmType;
            $updateData['pm_last_four'] = $pmLastFour;
        }

        $user->update($updateData);

    
    }

  
    private function handleSubscriptionDeleted($subscription)
    {
       

        $user = User::where('subscription_id', $subscription->id)->first();

        if ($user) {
            $endedAt = $subscription->ended_at
                ?: $subscription->canceled_at
                ?: $subscription->current_period_end
                ?: now()->timestamp;

            $user->update([
                'subscription_status' => 'canceled',
                'subscription_ends_at' => Carbon::createFromTimestamp($endedAt),
            ]);

            Log::info('Subscription deactivated', [
                'user_id' => $user->id,
                'subscription_ends_at' => $endedAt,
                'subscription_ends_at_formatted' => date('Y-m-d H:i:s', $endedAt)
            ]);
        } else {
            Log::warning('User not found for subscription deletion', ['subscription_id' => $subscription->id]);
        }
    }

 
    private function handleInvoicePaid($invoice)
    {
        Log::info('Processing invoice.paid', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription ?? null
        ]);

        if (!$invoice->subscription) {
            Log::info('Invoice not related to subscription, skipping');
            return;
        }

        $user = User::where('stripe_id', $invoice->customer)->first();

        if (!$user) {
            Log::warning('User not found for invoice', ['customer' => $invoice->customer]);
            return;
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $subscription = \Stripe\Subscription::retrieve($invoice->subscription);

            $updateData = [
                'shipments_used_this_month' => 0,
                'shipment_counter_reset_at' => now(),
            ];

            if ($subscription->current_period_end) {
                $updateData['current_period_end'] = Carbon::createFromTimestamp($subscription->current_period_end);
                Log::info('Updated current_period_end from invoice.paid', [
                    'current_period_end' => $subscription->current_period_end
                ]);
            }

            if ($user->subscription_status === 'past_due') {
                $updateData['subscription_status'] = 'active';
                Log::info('Subscription reactivated from past_due', ['user_id' => $user->id]);
            }

            if ($subscription->cancel_at_period_end && $subscription->current_period_end) {
                $updateData['subscription_ends_at'] = Carbon::createFromTimestamp($subscription->current_period_end);
                Log::info('Subscription still scheduled for cancellation', [
                    'ends_at' => $subscription->current_period_end
                ]);
            } elseif (!$subscription->cancel_at_period_end) {
                $updateData['subscription_ends_at'] = null;
            }

            $user->update($updateData);
        } catch (\Exception $e) {
            Log::error('Error processing invoice.paid', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);
        }

        Log::info('Invoice processed', ['user_id' => $user->id]);
    }

  
    private function handleInvoicePaymentFailed($invoice)
    {
        Log::warning('Processing invoice.payment_failed', [
            'invoice_id' => $invoice->id,
            'attempt_count' => $invoice->attempt_count ?? 1,
            'subscription_id' => $invoice->subscription ?? null
        ]);

        if (!$invoice->subscription) {
            Log::info('Invoice not related to subscription, skipping');
            return;
        }

        $user = User::where('stripe_id', $invoice->customer)->first();

        if ($user) {
            $user->update([
                'subscription_status' => 'past_due'
            ]);

            Log::warning('Subscription marked as past_due', [
                'user_id' => $user->id,
                'subscription_id' => $user->subscription_id
            ]);
        }
    }

  
    private function getPlanFromPriceId(string $priceId): string
    {
        $basicPriceId = config('subscription.plans.basic.price_id');
        $proPriceId = config('subscription.plans.pro.price_id');

        if ($priceId === $basicPriceId) {
            return 'basic';
        }
        if ($priceId === $proPriceId) {
            return 'pro';
        }

        return 'basic';
    }
}
