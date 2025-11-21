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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique();
            $table->string('phone')->unique();
            $table->string("full_name")->nullable();
            $table->string('email')->nullable();
            $table->boolean("delivery")->default(0);
            $table->string("national_id")->nullable();
            $table->string('id_front')->nullable();
            $table->string('id_back')->nullable();
            $table->string('selfie')->nullable();
            $table->string("password");
            $table->boolean('verified')->default(0);
            $table->string('last_otp')->nullable();
            $table->string('last_otp_expire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
