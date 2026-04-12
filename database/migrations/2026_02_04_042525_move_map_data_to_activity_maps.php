<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('athlete_activities')
            ->whereNotNull('map')
            ->orderBy('id')
            ->chunk(200, function ($activities) {
                foreach ($activities as $activity) {
                    DB::table('athlete_activity_maps')->insert([
                        'athlete_activity_id' => $activity->activity_id,
                        'activity_id' => $activity->id,
                        'athlete_id' => $activity->athlete_id,
                        'map' => $activity->map,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('athlete_activity_maps')->truncate();
    }
};
