<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class IndexScheduleTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|min:2|max:255',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}