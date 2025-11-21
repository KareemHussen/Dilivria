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
        Schema::table('customers', function (Blueprint $table) {
            $table->float('delivery_rate')->default(0)->after('lat');
            $table->float('customer_rate')->default(0)->after('delivery_rate');
            $table->string('fcm_token')->nullable()->after('customer_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['delivery_rate', 'customer_rate', 'fcm_token']);
        });
    }
};
