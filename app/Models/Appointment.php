<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;
use Dotenv\Util\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;
    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'patient_id',
        'start_time',
        'end_time',
        'appointment_date',
        'active',
        'notes',
        'dni',
        'phone',
        'passport',
        'insurance_code',
        'insurance_id',
        'guest_firstname',
        'guest_lastname',
        'status',
        'payment_type',
        'authorization_number',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'active' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function confirm(User $user, ?String $authNumber = null)
    {
        $this->status = 'confirmada';
        $this->confirmed_at = now();
        $this->confirmed_by = $user->id();
        $this->authorization_number = $authNumber;
        if ($authNumber) {
            $this->authorization_number = $authNumber;
        }
        $this->save();
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmada';
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
