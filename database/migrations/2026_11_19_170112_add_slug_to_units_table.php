<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('slug', 50)->nullable()->after('name');
            $table->string('type', 20)->default('standard')->after('slug'); // standard, weight, carton
        });

        // Seed the default units that match the existing hardcoded size_mode values
        $units = [
            ['name' => 'Pcs',       'slug' => 'by_pieces',  'type' => 'standard'],
            ['name' => 'Carton',    'slug' => 'by_cartons', 'type' => 'carton'],
            ['name' => 'Bundal',    'slug' => 'by_bandal',  'type' => 'carton'],
            ['name' => 'Meter',     'slug' => 'by_meter',   'type' => 'standard'],
            ['name' => 'Ft (Feet)', 'slug' => 'by_feet',    'type' => 'standard'],
            ['name' => 'Kg',        'slug' => 'by_kg',      'type' => 'weight'],
            ['name' => 'Gm',        'slug' => 'by_gm',      'type' => 'weight'],
            ['name' => 'Ton',       'slug' => 'by_ton',     'type' => 'weight'],
        ];

        foreach ($units as $unit) {
            $id = DB::table('units')->insertGetId(array_merge($unit, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Automatically sync existing products on live database
            DB::table('products')->where('size_mode', $unit['slug'])->whereNull('unit_id')->update(['unit_id' => $id]);
        }
    }

    public function down(): void
    {
        // Remove seeded units
        DB::table('units')->whereIn('slug', [
            'by_pieces','by_cartons','by_bandal','by_meter','by_feet','by_kg','by_gm','by_ton'
        ])->delete();

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['slug', 'type']);
        });
    }
};
