<?php

namespace App\Models;

use App\Traits\HasActiveToggle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory, HasActiveToggle;
    
    protected $table = 'role_user';

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
