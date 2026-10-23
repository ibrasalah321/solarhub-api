<?php

namespace App\Http\Resources\Rating;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerRatingResource extends JsonResource
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

            'engineer_id' => $this->engineer_id,

            'engineer' => $this->whenLoaded('engineer', function () {
                return [
                    'id' => $this->engineer->id,
                    'name' => $this->engineer->user?->name ?? 'مهندس معتمد',
                ];
            }),

            'service_request_id' => $this->service_request_id,

            'rating' => $this->rating,

            'comment' => $this->comment,

            'is_approved' => $this->is_approved,

            'created_at' => $this->created_at,
        ];
    }
}
