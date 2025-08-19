<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;

class ProcedureDiagnostic extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;

    public $timestamps = true;

    protected $fillable = [
        'procedure_id',
        'diagnostic_id',
        'description',
        'type',
        'severity',
        'chronic',
        'standard',
        'active',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function diagnostic()
    {
        return $this->belongsTo(DiagnosticStandard::class);
    }
}
