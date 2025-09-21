<?php

namespace App\Validators\Employee;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InsuranceValidator
{
    public static function validate(array $filters): array
    {
        $validator = Validator::make($filters, [
            'active'      => 'sometimes|in:true,false,1,0',
            'name'        => 'sometimes|string|max:100',
            'provider_code'        => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
