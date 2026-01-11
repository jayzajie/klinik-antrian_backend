<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->date('queue_date');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->integer('queue_number');
            $table->enum('status', ['waiting', 'called', 'skipped', 'cancelled', 'done'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->timestamps();

            $table->unique(['queue_date', 'department_id', 'queue_number']);
            $table->unique(['queue_date', 'department_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
