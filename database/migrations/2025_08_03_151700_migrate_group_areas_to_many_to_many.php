<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing area_of_interest_id data to the new pivot table
        $groups = DB::table('groups')
            ->whereNotNull('area_of_interest_id')
            ->get();
        
        foreach ($groups as $group) {
            DB::table('group_area_of_interest')->insert([
                'group_id' => $group->id,
                'area_of_interest_id' => $group->area_of_interest_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Note: We're keeping the old area_of_interest_id column for now
        // It can be removed in a future migration after ensuring everything works
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the pivot table
        DB::table('group_area_of_interest')->truncate();
    }
};