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
        Schema::table('order_negotiations', function (Blueprint $table) {
            $table->renameColumn('proposed_by', 'customer_id');
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('delivery_id')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_negotiations', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'proposed_by');
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->dropColumn('delivery_id');
        });
    }
};
