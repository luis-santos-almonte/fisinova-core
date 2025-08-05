<?php

namespace  App\Traits;

trait HasActiveScope
{
    public function scopeActive($query, $active)
    {
        return $query->where('active', $active);
    }
}
