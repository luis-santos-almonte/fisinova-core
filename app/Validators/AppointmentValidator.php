<?php

namespace App\Validators\Appointment;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AppointmentFilterValidator
{
    public static function validate(array $filters): array
    {
        $validator = Validator::make($filters, [
            'paginate'      => 'sometimes|integer|min:1|max:100',
            'active'        => 'sometimes|in:true,false,1,0',
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date|after_or_equal:start_date',
            'employee_id'   => 'sometimes|integer|exists:employees,id',
            'patient_id'    => 'sometimes|integer|exists:patients,id',
            'insurance_id'  => 'sometimes|integer|exists:insurances,id',
            'dni'           => 'sometimes|string|max:20',
            'phone'         => 'sometimes|string|max:20',
            'passport'      => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
