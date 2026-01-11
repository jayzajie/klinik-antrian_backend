<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueNote extends Model
{
    protected $fillable = [
        'queue_id',
        'doctor_id',
        'diagnosis',
        'prescription',
        'notes',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
