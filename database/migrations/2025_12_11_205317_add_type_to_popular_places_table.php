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
        Schema::table('popular_places', function (Blueprint $table) {
            $table->enum('type', [
                'restaurant', 
                'pharmacy', 
                'market', 
                'gas_station', 
                'metro_station', 
                'hospital', 
                'bank', 
                'school', 
                'mall', 
                'cafe', 
                'hotel', 
                'park', 
                'cinema', 
                'other'
            ])->nullable()->after('lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popular_places', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
