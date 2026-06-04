<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasFactory, Billable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'stripe_id',
        'subscription_id',
        'subscription_status',
        'subscription_plan',
        'subscription_ends_at',
        'current_period_end',
        'trial_ends_at',
        'shipments_used_this_month',
        'shipment_counter_reset_at',
        'pm_type',
        'pm_last_four',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'current_period_end' => 'datetime',
        'shipment_counter_reset_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];


    public function shipmentsCreated()
    {
        return $this->hasMany(Shipment::class, 'created_by');
    }

    public function addresses()
    {
        return $this->hasMany(Addresses::class);
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'user.' . $this->id;
    }


    
    public function hasActiveSubscription(): bool
    {
        if (in_array($this->subscription_status, ['active', 'trialing'])) {
            return true;
        }

        if (
            $this->subscription_status === 'cancelled' &&
            $this->subscription_ends_at &&
            !$this->subscription_ends_at->isPast()
        ) {
            return true;
        }

        return false;
    }

    public function isSubscriptionPastDue(): bool
    {
        return $this->subscription_status === 'past_due';
    }

    public function isSubscriptionCancelled(): bool
    {
        return in_array($this->subscription_status, ['cancelled', 'canceled']);
    }

   
    public function isSubscriptionIncomplete(): bool
    {
        return in_array($this->subscription_status, ['incomplete', 'incomplete_expired']);
    }

  
    public function isInactive(): bool
    {
        return in_array($this->subscription_status, [
            'inactive',
            'incomplete_expired',
            'unpaid',
            'paused',
        ]);
    }


    public function canCreateShipment(): bool
    {
        if ($this->subscription_status === 'cancelled') {
            if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) {
                return false;
            }
            return true;
        }

        if ($this->subscription_status === 'unpaid') {
            return false;
        }

        if ($this->subscription_status === 'paused') {
            return false;
        }

        if ($this->subscription_status === 'incomplete_expired') {
            return false;
        }

        if (!$this->hasActiveSubscription()) {
            return false;
        }

        if ($this->subscription_plan === 'pro') {
            return true;
        }

        $basicLimit = config('subscription.basic_shipment_limit', 10);

        return $this->shipments_used_this_month < $basicLimit;
    }

    public function getCannotCreateShipmentReason(): string
    {
        if (in_array($this->subscription_status, ['inactive', 'incomplete_expired'])) {
            return 'You need to subscribe to a plan to create shipments.';
        }

        if ($this->subscription_status === 'incomplete') {
            return 'Your subscription payment is still being confirmed. Please wait a moment.';
        }

        if ($this->subscription_status === 'past_due') {
            return 'Your payment is past due. Please update your payment method.';
        }

        if ($this->isSubscriptionCancelled()) {
            if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) {
                return 'Your subscription has expired. Please subscribe again to continue.';
            }
            return 'Your subscription has been cancelled. Please reactivate to continue.';
        }

        if ($this->subscription_status === 'unpaid') {
            return 'Your subscription has been cancelled due to failed payments. Please resubscribe.';
        }

        if ($this->subscription_status === 'paused') {
            return 'Your subscription is paused. Please add a payment method to continue.';
        }

        if ($this->hasActiveSubscription() && $this->subscription_plan === 'basic') {
            if ($this->getRemainingShipments() === 0) {
                return 'You have reached your monthly shipment limit. Please upgrade to Pro plan.';
            }
        }

        return 'Cannot create shipment at this time.';
    }


    public function resetMonthlyCounterIfNeeded(): void
    {
        $now = now();

        if (
            !$this->shipment_counter_reset_at ||
            $this->shipment_counter_reset_at->month !== $now->month ||
            $this->shipment_counter_reset_at->year !== $now->year
        ) {
            $this->update([
                'shipments_used_this_month' => 0,
                'shipment_counter_reset_at' => $now,
            ]);
        }
    }

    public function incrementShipmentUsage(): void
    {
        $this->increment('shipments_used_this_month');
    }

    public function getRemainingShipments(): int
    {
        if ($this->subscription_plan === 'pro') {
            return PHP_INT_MAX;
        }

        $limit = config('subscription.basic_shipment_limit', 10);

        return max(0, $limit - $this->shipments_used_this_month);
    }


    public function getPlanNameAttribute(): string
    {
        if (!$this->subscription_plan) {
            return 'No Plan';
        }
        return $this->subscription_plan === 'pro' ? 'Pro Plan' : 'Basic Plan';
    }

    public function getSubscriptionStatusBadgeAttribute(): string
    {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'trialing' => '<span class="badge bg-info">Trialing</span>',
            'incomplete' => '<span class="badge bg-warning">Pending Payment</span>',
            'incomplete_expired' => '<span class="badge bg-danger">Expired</span>',
            'past_due' => '<span class="badge bg-warning">Past Due</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            'unpaid' => '<span class="badge bg-danger">Unpaid</span>',
            'paused' => '<span class="badge bg-secondary">Paused</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        ];

        return $badges[$this->subscription_status]
            ?? '<span class="badge bg-secondary">Inactive</span>';
    }

    public function getSubscriptionStatusColor(): string
    {
        $colors = [
            'active' => 'success',
            'trialing' => 'info',
            'incomplete' => 'warning',
            'incomplete_expired' => 'error',
            'past_due' => 'warning',
            'cancelled' => 'error',
            'unpaid' => 'error',
            'paused' => 'default',
            'inactive' => 'default',
        ];

        return $colors[$this->subscription_status] ?? 'default';
    }


    public function setCurrentPeriodEndAttribute($value): void
    {
        $this->attributes['current_period_end'] = $this->parseTimestamp($value);
    }

    public function setSubscriptionEndsAtAttribute($value): void
    {
        $this->attributes['subscription_ends_at'] = $this->parseTimestamp($value);
    }

    public function setTrialEndsAtAttribute($value): void
    {
        $this->attributes['trial_ends_at'] = $this->parseTimestamp($value);
    }

    private function parseTimestamp($value): ?string
    {
        if ($value === null)
            return null;
        if ($value instanceof Carbon)
            return $value->toDateTimeString();
        if (is_numeric($value) && $value > 0)
            return Carbon::createFromTimestamp($value)->toDateTimeString();
        if (is_string($value) && strtotime($value))
            return Carbon::parse($value)->toDateTimeString();
        return null;
    }
}