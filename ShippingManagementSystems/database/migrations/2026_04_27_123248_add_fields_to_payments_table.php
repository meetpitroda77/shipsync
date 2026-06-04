<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->nullable()->after('amount');
            $table->decimal('tax', 10, 2)->nullable()->after('subtotal');
            $table->decimal('insurance', 10, 2)->nullable()->after('tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax', 'insurance']);
        });
    }
};