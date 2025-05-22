<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActiveToggle;

class Patient extends Model
{
    use HasFactory, HasActiveToggle;

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
        'active'
    ];

    protected $casts = [
        'birthdate' => 'date',
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
