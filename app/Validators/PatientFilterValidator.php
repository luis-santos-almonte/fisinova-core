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
            'search' => 'sometimes|string|min:2|max:255',
            'city' => 'sometimes|string|max:255',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }
}
