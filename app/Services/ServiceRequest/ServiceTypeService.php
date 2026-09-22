<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Collection;

class ServiceTypeService
{
    public function getActiveServiceTypes(): Collection
    {
        return ServiceType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}