<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class QueueSettingController extends Controller
{
    public function index()
    {
        $settings = QueueSetting::with('department')->get();
        return response()->json($settings);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
            'max_queue_per_day' => 'required|integer|min:1',
            'average_service_minutes' => 'required|integer|min:1',
        ]);

        $setting = QueueSetting::create($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_queue_setting',
            'model_type' => QueueSetting::class,
            'model_id' => $setting->id,
            'new_values' => $setting->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($setting->load('department'), 201);
    }

    public function update(Request $request, $id)
    {
        $setting = QueueSetting::findOrFail($id);
        
        $request->validate([
            'opening_time' => 'date_format:H:i',
            'closing_time' => 'date_format:H:i',
            'max_queue_per_day' => 'integer|min:1',
            'average_service_minutes' => 'integer|min:1',
        ]);

        $oldValues = $setting->toArray();
        $setting->update($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_queue_setting',
            'model_type' => QueueSetting::class,
            'model_id' => $setting->id,
            'old_values' => $oldValues,
            'new_values' => $setting->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($setting->load('department'));
    }
}
