<?php

namespace App\Http\Resources\ServiceRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                ];
            }),

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

            'system_capacity_estimate' =>
                $this->system_capacity_estimate,

            'attachment_file' =>
                $this->attachment_file,

            'location_details' =>
                $this->location_details,

            'location_coordinates' =>
                $this->location_coordinates,

            'description' =>
                $this->description,

            'status' =>
                $this->status,

            'created_at' =>
                $this->created_at,
        ];
    }
}