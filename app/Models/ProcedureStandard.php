<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;

class ProcedureStandard extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;
    public $timestamps = true;

    protected $fillable = [
        'description',
        'category',
        'standard',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function procedureDetails()
    {
        return $this->hasMany(ProcedureDetail::class);
    }

    public function procedureDiagnostics()
    {
        return $this->hasMany(ProcedureDiagnostic::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
