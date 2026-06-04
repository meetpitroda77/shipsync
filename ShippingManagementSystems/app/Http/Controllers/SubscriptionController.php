<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SubscriptionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer as StripeCustomer;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function createCheckout(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:basic,pro',
        ]);

        $user = $request->user();
        $plan = config('subscription.plans.' . $request->plan);

        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Invalid plan selected.'], 400);
        }

        if (empty($plan['price_id']) || $plan['price_id'] === 'price_xxxxxxxxxxxxx') {
            return response()->json([
                'success' => false,
                'message' => 'Price ID not configured for ' . $request->plan . ' plan.',
            ], 500);
        }

        if ($user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription.',
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $customerId = $user->stripe_id;

            if ($customerId) {
                try {
                    $customer = StripeCustomer::retrieve($customerId);
                    if (!$customer || $customer->isDeleted()) {
                        $customerId = null;
                        $user->update(['stripe_id' => null]);
                    }
                } catch (\Exception $e) {
                    $customerId = null;
                    $user->update(['stripe_id' => null]);
                }
            }

            if (!$customerId) {
                $customer = StripeCustomer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'metadata' => ['user_id' => $user->id],
                ]);
                $customerId = $customer->id;
                $user->update(['stripe_id' => $customerId]);
            }

            $subscriptionData = [
                'payment_behavior' => 'allow_incomplete',
                'trial_period_days' => 7,

            ];



            $session = StripeSession::create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'line_items' => [['price' => $plan['price_id'], 'quantity' => 1]],
                'mode' => 'subscription',
                'subscription_data' => $subscriptionData,
                'success_url' => config('app.frontend_url')
                    . "/subscription/success?session_id={CHECKOUT_SESSION_ID}&plan=" . $request->plan,
                'cancel_url' => config('app.frontend_url')
                    . "/subscription/cancel?plan=" . $request->plan,
                'metadata' => ['user_id' => $user->id, 'plan' => $request->plan],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $session->id,
                    'checkout_url' => $session->url,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Stripe error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $invoices = [];

        if ($user->stripe_id) {
            Stripe::setApiKey(config('services.stripe.secret'));
            try {
                $stripeInvoices = \Stripe\Invoice::all(['customer' => $user->stripe_id, 'limit' => 12]);
                $invoices = collect($stripeInvoices->data)->map(fn($inv) => [
                    'id' => $inv->id,
                    'number' => $inv->number ?? 'INV-' . substr($inv->id, 0, 8),
                    'created' => $inv->created,
                    'amount_paid' => $inv->amount_paid,

                    'status' => $inv->status,

                    'paid' => $inv->status === 'paid',

                    'invoice_pdf' => $inv->invoice_pdf,
                ])->toArray();
            } catch (\Exception $e) {
                Log::error('Error fetching invoices: ' . $e->getMessage());
            }
        }

        $status = $user->subscription_status;
        $plan = $user->subscription_plan;
        $periodEnd = $user->current_period_end?->timestamp;
        $endsAt = $user->subscription_ends_at?->timestamp;
        $trialEnd = $user->trial_ends_at?->timestamp;

        $isCancelled = $user->isSubscriptionCancelled();
        $displayPeriodEnd = ($isCancelled && $endsAt) ? $endsAt : $periodEnd;
        $resetDate = $user->shipment_counter_reset_at?->copy()->addMonth()->timestamp;

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => [
                    'plan' => $plan,
                    'plan_name' => $plan === 'pro' ? 'Pro Plan' : 'Basic Plan',
                    'status' => $status,
                    'current_period_end' => $displayPeriodEnd,
                    'ends_at' => $endsAt,
                    'trial_ends_at' => $trialEnd,
                    'is_trialing' => $status === 'trialing',
                    'is_cancelled' => $isCancelled,
                    'is_past_due' => $status === 'past_due',
                    'is_incomplete' => in_array($status, ['incomplete', 'incomplete_expired']),
                    'is_inactive' => $user->isInactive(),
                ],
                'usage' => [
                    'shipments_used' => $user->shipments_used_this_month,
                    'shipments_remaining' => $user->getRemainingShipments(),
                    'limit' => $plan === 'pro'
                        ? 'Unlimited'
                        : config('subscription.basic_shipment_limit', 10),
                    'reset_date' => $resetDate,
                ],
                'payment_method' => [
                    'last_four' => $user->pm_last_four,
                    'type' => $user->pm_type,
                ],
                'invoices' => $invoices,
                'can_upgrade' => $plan === 'basic' && $status === 'active',
            ],
        ]);
    }


    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        if ($user->subscription_status === 'cancelled' && $user->subscription_ends_at) {
            return response()->json([
                'success' => true,
                'message' => 'Subscription already cancelled.',
                'data' => [
                    'status' => 'cancelled',
                    'ends_at' => $user->subscription_ends_at?->timestamp,
                ]
            ]);
        }

        if (!in_array($user->subscription_status, ['active', 'trialing'])) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ], 400);
        }


        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            try {
                $stripeSubscription = \Stripe\Subscription::retrieve([
                    'id' => $user->subscription_id,
                    'expand' => ['default_payment_method'],
                ]);
            } catch (\Exception $e) {
                Log::warning('Subscription not found in Stripe, updating DB', [
                    'user_id' => $user->id,
                    'subscription_id' => $user->subscription_id
                ]);

                $user->update([
                    'subscription_status' => 'cancelled',
                    'subscription_ends_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription cancelled successfully.',
                    'data' => ['status' => 'cancelled']
                ]);
            }

            if ($stripeSubscription->status === 'canceled' || $stripeSubscription->canceled_at) {
                $endsAt = $stripeSubscription->canceled_at
                    ?? $stripeSubscription->ended_at
                    ?? $stripeSubscription->current_period_end;

                $user->update([
                    'subscription_status' => 'cancelled',
                    'subscription_ends_at' => $endsAt ? Carbon::createFromTimestamp($endsAt) : now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription already cancelled.',
                    'data' => ['status' => 'cancelled', 'ends_at' => $endsAt]
                ]);
            }

            if ($stripeSubscription->cancel_at_period_end) {
                $endsAt = $this->resolveEndsAt($stripeSubscription);
                $user->update([
                    'subscription_status' => 'cancelled',
                    'subscription_ends_at' => $endsAt,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription will be cancelled at period end.',
                    'data' => ['ends_at' => $endsAt?->timestamp, 'status' => 'cancelled'],
                ]);
            }

            $stripeSubscription->cancel_at_period_end = true;
            $stripeSubscription->save();

            $endsAt = $this->resolveEndsAt($stripeSubscription);

            $user->update([
                'subscription_status' => 'cancelled',
                'subscription_ends_at' => $endsAt,
            ]);

            try {
                $user->notify(new SubscriptionNotification('cancelled', $user->subscription_plan));
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription will be cancelled at period end.',
                'data' => ['ends_at' => $endsAt?->timestamp, 'status' => 'cancelled'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling subscription: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'No such subscription')) {
                $user->update([
                    'subscription_status' => 'cancelled',
                    'subscription_ends_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription cancelled successfully.',
                    'data' => ['status' => 'cancelled']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reactivateSubscription(Request $request)
    {
        $user = $request->user();

        if ($user->subscription_status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'No cancelled subscription found.',
            ], 400);
        }

        if (!$user->subscription_id) {
            return response()->json(['success' => false, 'message' => 'No subscription ID found.'], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($user->subscription_id);

            if (!$stripeSubscription->cancel_at_period_end) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is not scheduled for cancellation.',
                ], 400);
            }

            $stripeSubscription->cancel_at_period_end = false;
            $stripeSubscription->save();

            $correctStatus = $stripeSubscription->status === 'active' &&
                $stripeSubscription->trial_end &&
                $stripeSubscription->trial_end > now()->timestamp
                ? 'trialing'
                : $stripeSubscription->status;

            $updateData = [
                'subscription_status' => $correctStatus,
                'subscription_ends_at' => null,
            ];

            if ($correctStatus === 'trialing' && $stripeSubscription->trial_end) {
                $updateData['trial_ends_at'] = Carbon::createFromTimestamp($stripeSubscription->trial_end);
            } else {
                $updateData['trial_ends_at'] = null;
            }

            $user->update($updateData);

            try {
                $user->notify(new SubscriptionNotification('reactivated', $user->subscription_plan));
            } catch (\Exception $e) {
                Log::error('Failed to send reactivation notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription reactivated successfully.',
                'data' => ['status' => $correctStatus],
            ]);
        } catch (\Exception $e) {
            Log::error('Error reactivating subscription: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reactivating subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function upgradeToPro(Request $request)
    {
        $user = $request->user();

        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'You need an active subscription to upgrade.',
            ], 400);
        }

        if ($user->subscription_plan === 'pro') {
            return response()->json(['success' => false, 'message' => 'You are already on the Pro plan.'], 400);
        }

        if (!$user->subscription_id) {
            return response()->json(['success' => false, 'message' => 'No subscription found.'], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $proPriceId = config('subscription.plans.pro.price_id');
            $subscription = \Stripe\Subscription::retrieve($user->subscription_id);

            $updated = \Stripe\Subscription::update($user->subscription_id, [
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $proPriceId,
                    ]
                ],
                'proration_behavior' => 'create_prorations',
            ]);

            $periodEnd = $updated->current_period_end
                ?? ($updated->items->data[0]->current_period_end ?? null);


            $correctStatus = $updated->status;
            if (
                $updated->status === 'active' &&
                $updated->trial_end &&
                $updated->trial_end > now()->timestamp
            ) {
                $correctStatus = 'trialing';
            }

            $updateData = [
                'subscription_plan' => 'pro',
                'subscription_status' => $correctStatus,
                'current_period_end' => $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null,
                'shipments_used_this_month' => 0,
                'shipment_counter_reset_at' => now(),
            ];

            if ($correctStatus === 'trialing' && $updated->trial_end) {
                $updateData['trial_ends_at'] = Carbon::createFromTimestamp($updated->trial_end);
            } else {
                $updateData['trial_ends_at'] = null;
            }

            $user->update($updateData);

            try {
                $user->notify(new SubscriptionNotification('upgraded', 'pro', 'basic'));
            } catch (\Exception $e) {
                Log::error('Failed to send upgrade notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully upgraded to Pro plan!',
                'data' => [
                    'plan' => 'pro',
                    'status' => $correctStatus,
                    'next_billing_date' => $periodEnd,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error upgrading subscription: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upgrade subscription: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function billingPortal(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_id) {
            return response()->json(['success' => false, 'message' => 'No Stripe customer found.'], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $user->stripe_id,
                'return_url' => $request->input(
                    'return_url',
                    config('app.frontend_url') . '/customer/subscription/dashboard'
                ),
            ]);

            return response()->json(['success' => true, 'data' => ['url' => $session->url]]);
        } catch (\Exception $e) {
            Log::error('Billing portal error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error creating portal session.'], 500);
        }
    }


    public function getSubscriptionStatus(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No session ID provided'
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::retrieve($sessionId);

            \Log::info('Checking subscription status', [
                'session_id' => $sessionId,
                'session_status' => $session->status,
                'payment_status' => $session->payment_status,
                'has_subscription' => !empty($session->subscription)
            ]);

            if ($session->subscription) {
                $subscription = \Stripe\Subscription::retrieve($session->subscription);

                $user = User::where('stripe_id', $session->customer)->first();
                if ($user) {
                    $user->update([
                        'subscription_id' => $subscription->id,
                        'subscription_status' => $subscription->status,
                        'subscription_plan' => $session->metadata->plan ?? 'basic',
                        'current_period_end' => $subscription->current_period_end
                            ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_end)
                            : null,
                        'trial_ends_at' => $subscription->trial_end
                            ? \Carbon\Carbon::createFromTimestamp($subscription->trial_end)
                            : null,
                    ]);
                }

                $periodEnd = $subscription->current_period_end
                    ?? ($subscription->items->data[0]->current_period_end ?? null);

                return response()->json([
                    'status' => $subscription->status,
                    'subscription_id' => $subscription->id,
                    'plan' => $session->metadata->plan ?? 'basic',
                    'current_period_end' => $periodEnd,
                    'trial_end' => $subscription->trial_end,
                ]);
            }

            if ($session->payment_status === 'paid') {
                return response()->json([
                    'status' => 'processing',
                    'message' => 'Payment received, activating subscription...',
                ]);
            }

            if ($session->status === 'expired') {
                return response()->json([
                    'status' => 'expired',
                    'message' => 'Checkout session expired. Please try again.',
                ]);
            }

            return response()->json([
                'status' => 'pending',
                'message' => 'Waiting for payment confirmation',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking subscription status: ' . $e->getMessage(), [
                'session_id' => $sessionId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check subscription status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkCanCreateShipment(Request $request)
    {
        $user = $request->user();
        $user->resetMonthlyCounterIfNeeded();
        $canCreate = $user->canCreateShipment();

        return response()->json([
            'success' => true,
            'data' => [
                'can_create' => $canCreate,
                'reason' => $canCreate ? null : $user->getCannotCreateShipmentReason(),
            ],
        ]);
    }


    private function resolveEndsAt(\Stripe\Subscription $sub): ?Carbon
    {
        $periodEnd = $sub->current_period_end
            ?? ($sub->items->data[0]->current_period_end ?? null);

        if ($sub->cancel_at_period_end && $periodEnd) {
            return Carbon::createFromTimestamp($periodEnd);
        }

        return $periodEnd
            ? Carbon::createFromTimestamp($periodEnd)
            : now()->addDays(30);
    }
}
