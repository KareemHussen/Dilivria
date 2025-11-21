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
        Schema::create('order_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_order_id')->constrained('place_orders')->onDelete('cascade');
            $table->unsignedBigInteger('proposed_by'); // user ID of proposer (customer or delivery)
            $table->decimal('proposed_price', 8, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_negotiations');
    }
};
