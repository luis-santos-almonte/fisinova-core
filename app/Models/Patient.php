<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActiveToggle;

class Patient extends Model
{
    use HasFactory, HasActiveToggle, HasActiveScope;

    public $timestamps = true;
    protected $table = 'patients';

    protected $fillable = [
        'firstname',
        'lastname',
        'dni',
        'passport',
        'sex',
        'birthdate',
        'email',
        'phone',
        'cellphone',
        'address',
        'city',
        'insurance_code',
        'insurance_id',
        'active'
    ];

    protected $casts = [
        'birthdate' => 'date',
        'active' => 'boolean',
    ];

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
