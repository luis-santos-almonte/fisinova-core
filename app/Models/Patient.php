<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $timestamps = true;
    protected $table = 'patients';

    protected $fillable = [
        'name',
        'surname',
        'dni',
        'passport',
        'sex',
        'birthdate',
        'email',
        'phone',
        'cellphone',
        'address',
        'city',
    ];
}
