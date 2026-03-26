<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('places') || DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM places'))
            ->pluck('Key_name')
            ->all();

        if (! in_array('places_latitude_longitude_index', $indexes, true)) {
            DB::statement('CREATE INDEX places_latitude_longitude_index ON places (latitude, longitude)');
        }

        if (! in_array('places_experienced_at_index', $indexes, true)) {
            DB::statement('CREATE INDEX places_experienced_at_index ON places (experienced_at)');
        }

        if (! in_array('places_impression_index', $indexes, true)) {
            DB::statement('CREATE INDEX places_impression_index ON places (impression)');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('places') || DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM places'))
            ->pluck('Key_name')
            ->all();

        if (in_array('places_latitude_longitude_index', $indexes, true)) {
            DB::statement('DROP INDEX places_latitude_longitude_index ON places');
        }

        if (in_array('places_experienced_at_index', $indexes, true)) {
            DB::statement('DROP INDEX places_experienced_at_index ON places');
        }

        if (in_array('places_impression_index', $indexes, true)) {
            DB::statement('DROP INDEX places_impression_index ON places');
        }
    }
};
