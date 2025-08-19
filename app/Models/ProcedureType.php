<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;

class ProcedureType extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }
}
