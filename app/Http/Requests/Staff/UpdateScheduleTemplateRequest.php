<?php

namespace App\Http\Requests\Staff;
// namespace App\Http\Requests\ScheduleTemplate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'schedule_days' => 'sometimes|array|min:1',
            'schedule_days.*.day_of_week' => 'nullable|integer|min:1|max:7',
            'schedule_days.*.start_time' => 'required_with:schedule_days|date_format:H:i',
            'schedule_days.*.end_time' => 'required_with:schedule_days|date_format:H:i|after:schedule_days.*.start_time',
            'schedule_days.*.is_recurring' => 'sometimes|boolean',
        ];
    }
}