<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@klinik.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $departments = [
            ['name' => 'Poli Umum', 'description' => 'Pelayanan kesehatan umum', 'is_active' => true],
            ['name' => 'Poli Gigi', 'description' => 'Pelayanan kesehatan gigi dan mulut', 'is_active' => true],
            ['name' => 'Poli Anak', 'description' => 'Pelayanan kesehatan anak', 'is_active' => true],
            ['name' => 'Poli Mata', 'description' => 'Pelayanan kesehatan mata', 'is_active' => true],
        ];

        foreach ($departments as $dept) {
            \App\Models\Department::create($dept);
        }

        $doctors = [
            ['department_id' => 1, 'name' => 'Dr. Ahmad Fauzi', 'specialization' => 'Dokter Umum', 'phone' => '081234567890', 'is_active' => true],
            ['department_id' => 2, 'name' => 'Dr. Siti Nurhaliza', 'specialization' => 'Dokter Gigi', 'phone' => '081234567891', 'is_active' => true],
            ['department_id' => 3, 'name' => 'Dr. Budi Santoso', 'specialization' => 'Dokter Anak', 'phone' => '081234567892', 'is_active' => true],
            ['department_id' => 4, 'name' => 'Dr. Dewi Lestari', 'specialization' => 'Dokter Mata', 'phone' => '081234567893', 'is_active' => true],
        ];

        foreach ($doctors as $doc) {
            \App\Models\Doctor::create($doc);
        }

        $schedules = [
            ['doctor_id' => 1, 'department_id' => 1, 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '12:00', 'quota_per_day' => 30],
            ['doctor_id' => 1, 'department_id' => 1, 'day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '12:00', 'quota_per_day' => 30],
            ['doctor_id' => 1, 'department_id' => 1, 'day_of_week' => 5, 'start_time' => '08:00', 'end_time' => '12:00', 'quota_per_day' => 30],
            ['doctor_id' => 2, 'department_id' => 2, 'day_of_week' => 2, 'start_time' => '13:00', 'end_time' => '17:00', 'quota_per_day' => 20],
            ['doctor_id' => 2, 'department_id' => 2, 'day_of_week' => 4, 'start_time' => '13:00', 'end_time' => '17:00', 'quota_per_day' => 20],
            ['doctor_id' => 3, 'department_id' => 3, 'day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '14:00', 'quota_per_day' => 25],
            ['doctor_id' => 3, 'department_id' => 3, 'day_of_week' => 4, 'start_time' => '09:00', 'end_time' => '14:00', 'quota_per_day' => 25],
            ['doctor_id' => 4, 'department_id' => 4, 'day_of_week' => 3, 'start_time' => '10:00', 'end_time' => '15:00', 'quota_per_day' => 15],
        ];

        foreach ($schedules as $schedule) {
            \App\Models\DoctorSchedule::create($schedule);
        }
    }
}
