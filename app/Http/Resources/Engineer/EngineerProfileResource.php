<?php

namespace App\Http\Resources\Engineer;

use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storage = app(SupabaseStorageService::class);
        $photoUrl = $this->profile_photo_path ? $storage->publicUrl($this->profile_photo_path) : null;
        // السيرة الذاتية محفوظة في الحاوية الخاصة → رابط مؤقت، ويظهر فقط للمهندس نفسه أو للإدارة.
        $viewer = $request->user() ?? $request->user('sanctum');
        $canSeePrivate = $viewer && ($viewer->id === $this->user_id || $viewer->hasRole('admin'));
        $cvUrl = ($canSeePrivate && $this->cv_path) ? $storage->temporaryPrivateUrl($this->cv_path, 30) : null;

        return [
            'id' => $this->id,

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $canSeePrivate ? $this->user?->email : null,
                'phone' => $canSeePrivate ? $this->user?->phone : null,
                'governorate_id' => $this->user?->governorate_id,
                'governorate' => $this->user?->governorate?->name_ar ?? $this->user?->governorate?->name_en,
            ],

            'license_number' => $this->license_number,

            'years_of_experience' => $this->years_of_experience,

            'bio' => $this->bio,

            'profile_photo_path' => $this->profile_photo_path,
            'profile_photo_url' => $photoUrl,

            'cv_path' => $canSeePrivate ? $this->cv_path : null,
            'cv_url' => $cvUrl,

            'approval_status' => $this->approval_status,

            'specializations' => $this->whenLoaded('specializations', function () {
                return $this->specializations->map(function ($specialization) {
                    return [
                        'id' => $specialization->id,
                        'name' => $specialization->name,
                    ];
                });
            }, function () {
                return $this->specializations ? $this->specializations->map(function ($specialization) {
                    return [
                        'id' => $specialization->id,
                        'name' => $specialization->name,
                    ];
                }) : [];
            }),

            'created_at' => $this->created_at,
        ];
    }
}
