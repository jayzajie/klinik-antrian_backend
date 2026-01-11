<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\QueueSetting;
use Illuminate\Database\Seeder;

class QueueSettingSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();

        foreach ($departments as $department) {
            QueueSetting::create([
                'department_id' => $department->id,
                'opening_time' => '08:00:00',
                'closing_time' => '16:00:00',
                'max_queue_per_day' => 50,
                'average_service_minutes' => 15,
                'is_active' => true,
            ]);
        }
    }
}
