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
        Schema::create('place_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('address_from');
            $table->string('lng_from');
            $table->string('lat_from');
            $table->string('address_to');
            $table->string('lng_to');
            $table->string('lat_to');
            $table->decimal('price', 8, 2);
            $table->text('details');
            $table->enum('payment_method', ['cash', 'wallet']);
            $table->boolean('paid')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_orders');
    }
};
