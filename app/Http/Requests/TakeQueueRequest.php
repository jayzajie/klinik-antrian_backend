<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TakeQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'queue_date' => 'required|date|after_or_equal:today',
            'doctor_id' => 'nullable|exists:doctors,id',
        ];
    }
}
