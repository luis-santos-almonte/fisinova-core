<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'provider_code',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appoinntment::class);
    }
}
