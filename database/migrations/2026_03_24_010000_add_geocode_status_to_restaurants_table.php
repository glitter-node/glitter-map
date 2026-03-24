<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('geocode_status', 20)->default('pending')->after('longitude')->index();
            $table->timestamp('geocoded_at')->nullable()->after('geocode_status');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropIndex(['geocode_status']);
            $table->dropColumn(['geocode_status', 'geocoded_at']);
        });
    }
};
