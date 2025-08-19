<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasActiveToggle;

class Employee extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;

    public $timestamps = true;

    protected $fillable = [
        'firstname',
        'lastname',
        'dni',
        'cellphone',
        'phone',
        'email',
        'address',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedules::class);
    }
}
