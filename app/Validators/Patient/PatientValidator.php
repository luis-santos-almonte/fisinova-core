<?php

namespace App\Validators\Patient;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientFilterValidator
{
    public static function validate(array $data): array
    {
        $rules = [
            'active' => ['sometimes', Rule::in(['true', 'false'])],
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'city' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'paginate' => 'sometimes|integer|min:1',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }
}
