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
                    'name' => $this->governorate->name_ar ?? $this->governorate->name_en ?? '',
                    'name_ar' => $this->governorate->name_ar,
                    'name_en' => $this->governorate->name_en,
                ];
            }),

            'system_capacity_estimate' => $this->system_capacity_estimate,

            'attachment_file' => $this->attachment_file,

            'location_details' => $this->location_details,

            'location_coordinates' => $this->location_coordinates,

            'description' => $this->description,

            'status' => $this->status,

            // دورة حياة الطلب بعد قبول العرض
            'started_at' => $this->started_at,
            'engineer_completed_at' => $this->engineer_completed_at,
            'completed_at' => $this->completed_at,

            // تقييم العميل للمهندس (إن وُجد) حتى تعرف الواجهة هل تُظهر زر التقييم
            'rating' => $this->whenLoaded('rating', function () {
                return $this->rating ? [
                    'id' => $this->rating->id,
                    'rating' => $this->rating->rating,
                    'comment' => $this->rating->comment,
                    'created_at' => $this->rating->created_at,
                ] : null;
            }),

            'offers' => $this->whenLoaded('offers', function () {
                return $this->offers->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'engineer_id' => $offer->engineer_id,
                        'engineer_name' => $offer->relationLoaded('engineer') ? $offer->engineer?->user?->name : null,
                        'proposed_cost' => $offer->proposed_cost,
                        'execution_time_days' => $offer->execution_time_days,
                        'technical_proposal' => $offer->technical_proposal,
                        'proposal_file' => $offer->proposal_file,
                        'status' => $offer->status,
                    ];
                });
            }),

            'created_at' => $this->created_at,
        ];
    }
}
