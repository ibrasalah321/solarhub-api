<?php

namespace App\Http\Resources\ServiceRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'service_request_id' =>
                $this->service_request_id,

            'engineer' => $this->whenLoaded(
                'engineer',
                function () {
                    return [
                        'id' => $this->engineer->id,
                        'name' => $this->engineer->user?->name,
                        'profile_photo_path' =>
                            $this->engineer->profile_photo_path,
                        'years_of_experience' =>
                            $this->engineer->years_of_experience,
                    ];
                }
            ),

            'proposed_cost' =>
                $this->proposed_cost,

            'execution_time_days' =>
                $this->execution_time_days,

            'technical_proposal' =>
                $this->technical_proposal,

            'proposal_file' =>
                $this->proposal_file,

            'status' =>
                $this->status,

            'created_at' =>
                $this->created_at,
        ];
    }
}