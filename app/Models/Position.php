<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'active'
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
