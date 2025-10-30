<?php

namespace App\Services;

use App\Models\Cubicle;

class CubicleService
{
    public function getAllCubicles()
    {
        return Cubicle::where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    public function getCubicleById($id)
    {
        return Cubicle::findOrFail($id);
    }
}