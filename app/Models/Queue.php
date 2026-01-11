<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'queue_date',
        'department_id',
        'doctor_id',
        'patient_id',
        'queue_number',
        'status',
        'called_at',
        'done_at',
        'cancel_reason',
        'cancelled_at',
        'estimated_wait_minutes',
    ];

    protected $casts = [
        'queue_date' => 'date',
        'called_at' => 'datetime',
        'done_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function note()
    {
        return $this->hasOne(QueueNote::class);
    }
}
