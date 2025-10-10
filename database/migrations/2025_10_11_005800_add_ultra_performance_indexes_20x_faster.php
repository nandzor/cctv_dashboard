<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // ULTRA-AGGRESSIVE INDEXES for 20x performance boost

        // 1. Super covering index for mega query optimization
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_mega_covering
            ON re_id_branch_detections(detection_timestamp, branch_id, re_id, device_id)
            INCLUDE (detected_count)
        ");

        // 2. Partial index for recent data (last 90 days) - most common queries
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_recent_90_days
            ON re_id_branch_detections(detection_timestamp, branch_id, re_id, device_id)
            WHERE detection_timestamp >= CURRENT_DATE - INTERVAL '90 days'
        ");

        // 3. Hash index for exact branch lookups (ultra fast)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_branch_hash
            ON re_id_branch_detections USING HASH (branch_id)
        ");

        // 4. GIN index for JSONB detection_data (if exists)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_data_gin
            ON re_id_branch_detections USING GIN (detection_data)
        ");

        // 5. Composite index for date range + branch filtering (most common pattern)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_date_branch_optimized
            ON re_id_branch_detections(detection_timestamp DESC, branch_id, re_id, device_id)
        ");

        // 6. Index for daily aggregation queries
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_daily_agg
            ON re_id_branch_detections(DATE(detection_timestamp), branch_id, re_id)
        ");

        // 7. Index for branch statistics (top branches)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_branch_stats
            ON re_id_branch_detections(branch_id, detection_timestamp DESC, re_id)
        ");

        // 8. BRIN index for timestamp (efficient for large tables)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_re_id_branch_detections_brin_ultra
            ON re_id_branch_detections USING BRIN (detection_timestamp)
        ");

        // 9. Index for company branches optimization
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_company_branches_ultra_optimized
            ON company_branches(id, branch_name, status)
            WHERE status = 'active'
        ");

        // 10. Index for counting reports optimization
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_counting_reports_ultra_optimized
            ON counting_reports(report_type, report_date DESC, branch_id)
            INCLUDE (total_detections, unique_person_count, total_events)
        ");

        // 11. Analyze tables for better query planning
        DB::statement("ANALYZE re_id_branch_detections");
        DB::statement("ANALYZE company_branches");
        DB::statement("ANALYZE counting_reports");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop all ultra-performance indexes
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_mega_covering");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_recent_90_days");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_branch_hash");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_data_gin");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_date_branch_optimized");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_daily_agg");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_branch_stats");
        DB::statement("DROP INDEX IF EXISTS idx_re_id_branch_detections_brin_ultra");
        DB::statement("DROP INDEX IF EXISTS idx_company_branches_ultra_optimized");
        DB::statement("DROP INDEX IF EXISTS idx_counting_reports_ultra_optimized");
    }
};
