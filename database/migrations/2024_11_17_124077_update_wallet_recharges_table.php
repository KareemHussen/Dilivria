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
        Schema::table('wallet_recharges', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->enum('payment_type', ['vodafone_cash', 'instapay'])->after('photo');
            $table->string('payment_number')->after('payment_type'); // Phone number for Vodafone Cash or address for Instapay
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_recharges', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'payment_number']);
            $table->string('phone_number')->after('photo');
        });
    }
};
