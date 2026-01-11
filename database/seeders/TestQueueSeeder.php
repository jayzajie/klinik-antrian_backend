<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestQueueSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Test Patient',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'phone' => '081234567899',
            'address' => 'Jl. Test No. 123',
        ]);

        Queue::create([
            'queue_date' => now()->toDateString(),
            'department_id' => 1,
            'patient_id' => $patient->id,
            'queue_number' => 1,
            'status' => 'waiting',
        ]);

        Queue::create([
            'queue_date' => now()->toDateString(),
            'department_id' => 1,
            'patient_id' => $patient->id,
            'queue_number' => 2,
            'status' => 'called',
            'called_at' => now(),
        ]);
    }
}
