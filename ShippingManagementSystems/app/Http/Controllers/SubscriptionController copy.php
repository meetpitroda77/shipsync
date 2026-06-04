<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer as StripeCustomer;

class SubscriptionController extends Controller
{
    public function createCheckout(Request $request)
    {
        try {
            $request->validate([
                'plan' => 'required|in:basic,pro',
            ]);

            $user = $request->user();
            $plan = config('subscription.plans.' . $request->plan);

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid plan selected.',
                ], 400);
            }

            if (empty($plan['price_id']) || $plan['price_id'] === 'price_xxxxxxxxxxxxx') {
                Log::error('Price ID not configured for plan: ' . $request->plan);
                return response()->json([
                    'success' => false,
                    'message' => 'Price ID not configured for ' . $request->plan . ' plan.',
                ], 500);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            if (!$user->stripe_id) {
                $customer = StripeCustomer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'metadata' => ['user_id' => $user->id],
                ]);
                $user->stripe_id = $customer->id;
                $user->save();
            }

            if ($user->hasActiveSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription.',
                ], 400);
            }

            $successUrl = config('app.frontend_url') . "/subscription/success?session_id={CHECKOUT_SESSION_ID}&plan=" . $request->plan;
            $cancelUrl = config('app.frontend_url') . "/subscription/cancel?plan=" . $request->plan;

            $session = StripeSession::create([
                'customer' => $user->stripe_id,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $plan['price_id'],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $request->plan,
                ],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $session->id,
                    'checkout_url' => $session->url,
                ]
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
        $user->resetMonthlyCounterIfNeeded();

        $invoices = [];
        $currentPeriodEnd = null;
        $endsAt = null;
        $subscriptionStatus = $user->subscription_status;
        $subscriptionPlan = $user->subscription_plan;

        $shouldSyncWithStripe = $user->stripe_id &&
            $user->subscription_id &&
            $user->subscription_status === 'active';

        if ($shouldSyncWithStripe) {
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $stripeSubscription = \Stripe\Subscription::retrieve($user->subscription_id);

                if ($stripeSubscription) {
                    $currentPeriodEnd = $stripeSubscription->current_period_end;
                    $subscriptionStatus = $stripeSubscription->status;

                    if (isset($stripeSubscription->items->data[0]->price->id)) {
                        $priceId = $stripeSubscription->items->data[0]->price->id;
                        $subscriptionPlan = $this->getPlanFromPriceId($priceId);
                    }

                    if ($stripeSubscription->cancel_at_period_end) {
                        $endsAt = $stripeSubscription->current_period_end;
                        $subscriptionStatus = 'cancelled';
                    }

                    $user->subscription_status = $subscriptionStatus;
                    $user->subscription_plan = $subscriptionPlan;

                    if ($currentPeriodEnd) {
                        $user->current_period_end = \Carbon\Carbon::createFromTimestamp($currentPeriodEnd);
                    }

                    if ($endsAt) {
                        $user->subscription_ends_at = \Carbon\Carbon::createFromTimestamp($endsAt);
                    } elseif (!$stripeSubscription->cancel_at_period_end) {
                        $user->subscription_ends_at = null;
                    }

                    $user->save();

                    Log::info('Updated subscription from Stripe', [
                        'user_id' => $user->id,
                        'current_period_end' => $currentPeriodEnd,
                        'formatted' => $currentPeriodEnd ? date('Y-m-d H:i:s', $currentPeriodEnd) : null,
                        'status' => $subscriptionStatus
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error fetching Stripe subscription: ' . $e->getMessage());

                if ($user->current_period_end) {
                    $currentPeriodEnd = $user->current_period_end->timestamp;
                }
                if ($user->subscription_ends_at) {
                    $endsAt = $user->subscription_ends_at->timestamp;
                }
            }
        } else {
            if ($user->current_period_end) {
                $currentPeriodEnd = $user->current_period_end->timestamp;
            }
            if ($user->subscription_ends_at) {
                $endsAt = $user->subscription_ends_at->timestamp;
            }
            $subscriptionStatus = $user->subscription_status;
            $subscriptionPlan = $user->subscription_plan;
        }

        if ($user->stripe_id) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));

                $stripeInvoices = \Stripe\Invoice::all([
                    'customer' => $user->stripe_id,
                    'limit' => 12,
                ]);

                $invoices = collect($stripeInvoices->data)->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'number' => $invoice->number ?? 'INV-' . substr($invoice->id, 0, 8),
                        'created' => $invoice->created,
                        'amount_paid' => $invoice->amount_paid,
                        'paid' => $invoice->status === 'paid',
                        'invoice_pdf' => $invoice->invoice_pdf,
                    ];
                })->toArray();

                Log::info('Fetched invoices', [
                    'user_id' => $user->id,
                    'count' => count($invoices)
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching invoices: ' . $e->getMessage());
            }
        }

        $resetDate = null;
        if ($user->shipment_counter_reset_at) {
            $resetDate = $user->shipment_counter_reset_at->copy()->addMonth()->timestamp;
        }

        $displayPeriodEnd = $currentPeriodEnd;
        if (($subscriptionStatus === 'cancelled' || $subscriptionStatus === 'canceled') && $endsAt) {
            $displayPeriodEnd = $endsAt;
        }

        if (!$displayPeriodEnd && $subscriptionStatus === 'active') {
            $displayPeriodEnd = now()->addDays(30)->timestamp;
        }

        $data = [
            'subscription' => [
                'plan' => $subscriptionPlan,
                'plan_name' => $subscriptionPlan === 'pro' ? 'Pro Plan' : 'Basic Plan',
                'status' => $subscriptionStatus,
                'current_period_end' => $displayPeriodEnd,
                'ends_at' => $endsAt,
                'is_cancelled' => $subscriptionStatus === 'cancelled' || $subscriptionStatus === 'canceled',
                'is_past_due' => $subscriptionStatus === 'past_due',
                'is_inactive' => $subscriptionStatus === 'inactive' || $subscriptionStatus === 'incomplete' || $subscriptionStatus === 'incomplete_expired',
            ],
            'usage' => [
                'shipments_used' => $user->shipments_used_this_month,
                'shipments_remaining' => $user->getRemainingShipments(),
                'limit' => $subscriptionPlan === 'pro' ? 'Unlimited' : config('subscription.basic_shipment_limit', 10),
                'reset_date' => $resetDate,
            ],
            'payment_method' => [
                'last_four' => $user->pm_last_four,
            ],
            'invoices' => $invoices,
            'can_upgrade' => $subscriptionPlan === 'basic' && $subscriptionStatus === 'active',
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
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
    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        if ($user->subscription_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ], 400);
        }

        if (!$user->subscription_id) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription ID found.',
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $stripeSubscription = \Stripe\Subscription::retrieve($user->subscription_id);

          

            if ($stripeSubscription->cancel_at_period_end) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is already scheduled for cancellation.',
                ], 400);
            }

            $stripeSubscription->cancel_at_period_end = true;
            $stripeSubscription->save();

            $endsAt = null;

            if ($stripeSubscription->current_period_end) {
                $endsAt = \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end);
            } else {
                $endsAt = now()->addDays(30);

            }

            $user->update([
                'subscription_status' => 'cancelled',
                'subscription_ends_at' => $endsAt,
            ]);

          
            return response()->json([
                'success' => true,
                'message' => 'Subscription will be cancelled at period end.',
                'data' => [
                    'ends_at' => $endsAt->timestamp,
                    'status' => 'cancelled'
                ]
            ]);
        } catch (\Exception $e) {
          

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
            return response()->json([
                'success' => false,
                'message' => 'No subscription ID found.',
            ], 400);
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

            $user->update([
                'subscription_status' => 'active',
                'subscription_ends_at' => null,
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Subscription reactivated successfully.',
                'data' => [
                    'status' => 'active'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error reactivating subscription: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'subscription_id' => $user->subscription_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error reactivating subscription: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function upgradeToPro(Request $request)
    {
        $user = $request->user();

        if ($user->subscription_status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'You need an active subscription to upgrade.',
            ], 400);
        }

        if ($user->subscription_plan === 'pro') {
            return response()->json([
                'success' => false,
                'message' => 'You are already on the Pro plan.',
            ], 400);
        }

        if (!$user->subscription_id) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found.',
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $proPriceId = config('subscription.plans.pro.price_id');

            $subscription = \Stripe\Subscription::retrieve($user->subscription_id);

            $updatedSubscription = \Stripe\Subscription::update($user->subscription_id, [
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $proPriceId,
                    ]
                ],
                'proration_behavior' => 'create_prorations',
            ]);

            $user->update([
                'subscription_plan' => 'pro',
                'subscription_status' => $updatedSubscription->status,
                'current_period_end' => $updatedSubscription->current_period_end
                    ? \Carbon\Carbon::createFromTimestamp($updatedSubscription->current_period_end)
                    : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully upgraded to Pro plan!',
                'data' => [
                    'plan' => 'pro',
                    'status' => $updatedSubscription->status,
                    'next_billing_date' => $updatedSubscription->current_period_end,
                ]
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
            return response()->json([
                'success' => false,
                'message' => 'No Stripe customer found.',
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $user->stripe_id,
                'return_url' => $request->input('return_url', config('app.frontend_url') . '/customer/subscription/dashboard'),
            ]);

            return response()->json([
                'success' => true,
                'data' => ['url' => $session->url],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating portal session.',
            ], 500);
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

            if ($session->subscription) {
                $subscription = \Stripe\Subscription::retrieve($session->subscription);

                return response()->json([
                    'status' => $subscription->status,
                    'subscription_id' => $subscription->id,
                    'plan' => $session->metadata->plan ?? 'basic',
                    'current_period_end' => $subscription->current_period_end,
                ]);
            }

            if ($session->payment_status === 'paid') {
                return response()->json([
                    'status' => 'processing',
                    'message' => 'Payment received, activating subscription...'
                ]);
            }

            if ($session->payment_status === 'unpaid') {
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Waiting for payment'
                ]);
            }

            return response()->json([
                'status' => $session->payment_status,
                'message' => 'Waiting for payment confirmation'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking subscription status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function checkCanCreateShipment(Request $request)
    {
        $user = $request->user();

        $canCreate = $user->canCreateShipment();
        $reason = $canCreate ? null : $user->getCannotCreateShipmentReason();

        return response()->json([
            'success' => true,
            'data' => [
                'can_create' => $canCreate,
                'reason' => $reason,
            ]
        ]);
    }
}
