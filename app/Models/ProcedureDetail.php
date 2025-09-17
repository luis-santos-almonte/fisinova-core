<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;

class ProcedureDetail extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;

    public $timestamps = true;

    protected $fillable = [
        'procedure_id',
        'description',
        'notes',
        'amount',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function procedureStandard()
    {
        return $this->belongsTo(ProcedureStandard::class);
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
