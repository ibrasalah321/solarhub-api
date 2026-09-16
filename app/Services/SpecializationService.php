<?php

namespace App\Services;

use App\Models\Specialization;
use Illuminate\Database\Eloquent\Collection;

class SpecializationService
{
    public function getActiveSpecializations(): Collection
    {
        return Specialization::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}