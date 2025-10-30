<?php

namespace App\Services;

use App\Models\Position;

class PositionService
{
    public function getAllPositions()
    {
        return Position::where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getPositionById($id)
    {
        return Position::findOrFail($id);
    }
}