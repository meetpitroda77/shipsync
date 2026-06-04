<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Carbon\Carbon;

class SyncStripeSubscriptions extends Command
{
  protected $signature = 'stripe:sync-subscriptions';
  protected $description = 'Sync all user subscriptions from Stripe';

  public function handle()
  {
    Stripe::setApiKey(config('services.stripe.secret'));

    $users = User::whereNotNull('subscription_id')->get();

    $statusMap = [
      'active' => 'active',
      'trialing' => 'trialing',
      'incomplete' => 'incomplete',
      'incomplete_expired' => 'incomplete_expired',
      'past_due' => 'past_due',
      'canceled' => 'cancelled',
      'unpaid' => 'unpaid',
      'paused' => 'paused',
    ];

    foreach ($users as $user) {
      try {
        $subscription = StripeSubscription::retrieve($user->subscription_id);

        $newStatus = $statusMap[$subscription->status] ?? 'inactive';

        if ($subscription->cancel_at_period_end && $subscription->status === 'active') {
          $newStatus = 'cancelled';
        }

        $updateData = [
          'subscription_status' => $newStatus,
          'current_period_end' => $subscription->current_period_end
            ? Carbon::createFromTimestamp($subscription->current_period_end)
            : null,
        ];

        if ($subscription->cancel_at_period_end && $subscription->current_period_end) {
          $updateData['subscription_ends_at'] = Carbon::createFromTimestamp($subscription->current_period_end);
        }

        if ($newStatus === 'active') {
          $updateData['subscription_ends_at'] = null;
        }

        $user->update($updateData);

        $this->info("Synced user {$user->id}: {$newStatus}");

      } catch (\Exception $e) {
        $this->error("Failed to sync user {$user->id}: " . $e->getMessage());
      }
    }

    $this->info('Subscription sync completed');
  }
}