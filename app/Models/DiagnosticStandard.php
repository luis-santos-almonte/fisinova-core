<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticStandard extends Model
{
    protected $fillable = [
        'description',
        'category',
        'standard',
        'grade',
        'chronic',
        'type',
        'code',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function procedureDiagnostics()
    {
        return $this->hasMany(ProcedureDiagnostic::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
