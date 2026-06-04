<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_id')->nullable()->after('stripe_id');
            $table->enum('subscription_status', [
                'active',
                'trialing',
                'incomplete',
                'incomplete_expired',
                'past_due',
                'cancelled',
                'unpaid',
                'paused',
                'inactive',
            ])->default('inactive')->after('subscription_id');

            $table->string('subscription_plan')->nullable()->after('subscription_status');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_plan');
            $table->timestamp('current_period_end')->nullable()->after('subscription_ends_at');
            $table->timestamp('trial_ends_at')->nullable()->after('current_period_end');

            $table->integer('shipments_used_this_month')->default(0)->after('trial_ends_at');
            $table->timestamp('shipment_counter_reset_at')->nullable()->after('shipments_used_this_month');

            $table->string('pm_type')->nullable()->after('shipment_counter_reset_at');
            $table->string('pm_last_four')->nullable()->after('pm_type');

            // Add indexes for performance
            $table->index('subscription_status');
            $table->index('subscription_plan');
            $table->index(['subscription_status', 'subscription_ends_at']);
            $table->index('subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
