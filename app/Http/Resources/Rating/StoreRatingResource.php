<?php

namespace App\Http\Resources\Rating;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_store_id' => $this->order_store_id,

            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ]),

            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->store->id,
                'company_name' => $this->store->company_name,
            ]),

            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
        ];
    }
}
