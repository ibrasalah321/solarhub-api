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

            'service_request_id' => $this->service_request_id,

            'rating' => $this->rating,

            'comment' => $this->comment,

            'created_at' => $this->created_at,
        ];
    }
}