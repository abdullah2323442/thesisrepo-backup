<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a view for admin-created groups that advisors can see
        DB::statement("
            CREATE VIEW admin_created_groups_view AS
            SELECT 
                g.id,
                g.name,
                g.batch_number,
                g.advisor_id,
                g.created_by_admin_id,
                g.advisor_auto_detected,
                g.area_of_interest_id,
                g.supervisor_id,
                g.max_students,
                g.is_manual_assignment,
                g.created_at,
                g.updated_at,
                admin_user.name as created_by_admin_name,
                advisor_user.name as advisor_name,
                COUNT(gs.id) as student_count,
                GROUP_CONCAT(gs.student_name, ', ') as student_names,
                aoi.name as area_of_interest_name,
                s.fullname as supervisor_name
            FROM groups g
            LEFT JOIN users admin_user ON g.created_by_admin_id = admin_user.id
            LEFT JOIN users advisor_user ON g.advisor_id = advisor_user.id
            LEFT JOIN group_students gs ON g.id = gs.group_id
            LEFT JOIN area_of_interests aoi ON g.area_of_interest_id = aoi.id
            LEFT JOIN supervisors s ON g.supervisor_id = s.id
            WHERE g.created_by_type = 'admin'
            GROUP BY g.id, g.name, g.batch_number, g.advisor_id, g.created_by_admin_id, 
                     g.advisor_auto_detected, g.area_of_interest_id, g.supervisor_id, 
                     g.max_students, g.is_manual_assignment, g.created_at, g.updated_at,
                     admin_user.name, advisor_user.name, aoi.name, s.fullname
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS admin_created_groups_view');
    }
};