<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'project_title' => $this->project_title,

            'service_type' => $this->whenLoaded('serviceType', function () {
                return [
                    'id' => $this->serviceType->id,
                    'name' => $this->serviceType->name,
                ];
            }),

            'governorate' => $this->whenLoaded('governorate', function () {
                return [
                    'id' => $this->governorate->id,
                    'name' => $this->governorate->name,
                ];
            }),

            'system_capacity' => $this->system_capacity,

            'description' => $this->description,

            'image_path' => $this->image_path,

            'file_path' => $this->file_path,

            'address_text' => $this->address_text,

            'location_coordinates' => $this->location_coordinates,

            'created_at' => $this->created_at,
        ];
    }
}