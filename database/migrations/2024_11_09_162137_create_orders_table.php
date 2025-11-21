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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('place_order_id');
            $table->unsignedBigInteger('delivery_id');
            $table->decimal('price', 8, 2);
            $table->float('rate')->nullable();
            $table->enum('status', ['waiting', 'first_point', 'received', 'sec_point', 'completed', 'cancelled_user', 'cancelled_delivery'])
            ->default('waiting');
            $table->dateTime('delivery_time')->nullable();
            $table->timestamps();

            $table->foreign('place_order_id')->references('id')->on('place_orders')->onDelete('cascade');
            $table->foreign('delivery_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
