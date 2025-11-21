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
        Schema::create('misc_pages', function (Blueprint $table) {
            $table->id();
            $table->mediumText('about')->nullable();
            $table->mediumText('privacy_terms')->nullable();
            $table->json('faq')->nullable();
            $table->string('contact_us')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('misc_pages');
    }
};
