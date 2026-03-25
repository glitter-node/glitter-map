<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('restaurants') || DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM restaurants'))
            ->pluck('Key_name')
            ->all();

        if (! in_array('restaurants_latitude_longitude_index', $indexes, true)) {
            DB::statement('CREATE INDEX restaurants_latitude_longitude_index ON restaurants (latitude, longitude)');
        }

        if (! in_array('restaurants_category_index', $indexes, true)) {
            DB::statement('CREATE INDEX restaurants_category_index ON restaurants (category)');
        }

        if (! in_array('restaurants_visited_at_index', $indexes, true)) {
            DB::statement('CREATE INDEX restaurants_visited_at_index ON restaurants (visited_at)');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('restaurants') || DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM restaurants'))
            ->pluck('Key_name')
            ->all();

        if (in_array('restaurants_latitude_longitude_index', $indexes, true)) {
            DB::statement('DROP INDEX restaurants_latitude_longitude_index ON restaurants');
        }

        if (in_array('restaurants_category_index', $indexes, true)) {
            DB::statement('DROP INDEX restaurants_category_index ON restaurants');
        }

        if (in_array('restaurants_visited_at_index', $indexes, true)) {
            DB::statement('DROP INDEX restaurants_visited_at_index ON restaurants');
        }
    }
};
