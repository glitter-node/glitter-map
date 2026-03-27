<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->double('latitude')->nullable()->after('geocode_status');
            $table->double('longitude')->nullable()->after('latitude');
            $table->timestamp('geocoded_at')->nullable()->after('longitude');
            $table->index(['latitude', 'longitude'], 'places_lat_lng_index');
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex('places_lat_lng_index');
            $table->dropColumn(['latitude', 'longitude', 'geocoded_at']);
        });
    }
};
