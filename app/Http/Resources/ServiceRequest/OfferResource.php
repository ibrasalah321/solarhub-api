<?php

namespace App\Http\Resources\ServiceRequest;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fileUrl = $this->proposal_file
            ? app(SupabaseStorageService::class)->temporaryPrivateUrl($this->proposal_file, 60)
            : null;

        return [
            'id' => $this->id,

            'service_request_id' => $this->service_request_id,

            'service_request' => $this->whenLoaded('serviceRequest', function () {
                return [
                    'id' => $this->serviceRequest->id,
                    'title' => $this->serviceRequest->description ? \Illuminate\Support\Str::limit($this->serviceRequest->description, 60) : "طلب خدمة #{$this->serviceRequest->id}",
                    'service_type' => $this->serviceRequest->serviceType?->name,
                    'governorate' => $this->serviceRequest->governorate?->name_ar ?? $this->serviceRequest->governorate?->name_en ?? '',
                    'system_capacity_estimate' => $this->serviceRequest->system_capacity_estimate,
                    'status' => $this->serviceRequest->status,
                ];
            }),

            'engineer' => $this->whenLoaded(
                'engineer',
                function () {
                    return [
                        'id' => $this->engineer->id,
                        'name' => $this->engineer->user?->name,
                        'profile_photo_path' => $this->engineer->profile_photo_path,
                        'years_of_experience' => $this->engineer->years_of_experience,
                    ];
                }
            ),

            'proposed_cost' => $this->proposed_cost,

            'execution_time_days' => $this->execution_time_days,

            'technical_proposal' => $this->technical_proposal,

            'proposal_file' => $this->proposal_file,
            'proposal_file_url' => $fileUrl,

            'status' => $this->status,

            'created_at' => $this->created_at,
        ];
    }
}
