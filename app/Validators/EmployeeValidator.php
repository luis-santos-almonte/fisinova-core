<?php

namespace App\Validators\Employee;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmployeeFilterValidator
{
    public static function validate(array $filters): array
    {
        $validator = Validator::make($filters, [
            'paginate'    => 'sometimes|integer|min:1|max:100',
            'active'      => 'sometimes|in:true,false,1,0',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'sometimes|date|after_or_equal:start_date',
            'dni'         => 'sometimes|string|max:20',
            'name'        => 'sometimes|string|max:100',
            'email'       => 'sometimes|email',
            'position_id' => 'sometimes|integer|exists:positions,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
