<?php

namespace App\Services\Engineer;

use App\Models\EngineerProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\SupabaseStorageService;

class EngineerProfileService
{
    /**
     * Get approved engineers.
     */
    public function __construct(private readonly SupabaseStorageService $storageService){

    }
    public function getApprovedEngineers(array $filters = []): LengthAwarePaginator
    {
        return EngineerProfile::query()
            ->with([
                'user',
                'specializations',
            ])
            ->withAvg(
                ['ratings' => function ($query) {
                    $query->where('is_approved', true);
                }],
                'rating'
            )
            ->withCount([
                'ratings' => function ($query) {
                    $query->where('is_approved', true);
                }
            ])
            ->where('approval_status', 'approved')
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $filters['specialization_id'] ?? null,
                function ($query, $specializationId) {
                    $query->whereHas(
                        'specializations',
                        function ($specializationQuery) use ($specializationId) {
                            $specializationQuery->where(
                                'specializations.id',
                                $specializationId
                            );
                        }
                    );
                }
            )
            ->when(
                $filters['governorate_id'] ?? null,
                function ($query, $governorateId) {
                    $query->whereHas(
                        'user',
                        function ($userQuery) use ($governorateId) {
                            $userQuery->where(
                                'governorate_id',
                                $governorateId
                            );
                        }
                    );
                }
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Get one approved engineer.
     */
    public function getApprovedEngineer(int $engineerId): EngineerProfile
    {
        return EngineerProfile::query()
            ->where('approval_status', 'approved')
            ->with([
                'user',
                'specializations',
                'certificates',
                'portfolioItems.serviceType',
                'portfolioItems.governorate',
                'ratings',
            ])
            ->findOrFail($engineerId);
    }

    /**
     * Get the authenticated engineer profile.
     */
    public function getMyProfile(User $user): ?EngineerProfile
    {
        return EngineerProfile::query()
            ->with([
                'user',
                'specializations',
                'certificates',
                'portfolioItems',
            ])
            ->where('user_id', $user->id)
            ->first();
    }

    public function updateProfile(EngineerProfile $engineer,array $data): EngineerProfile {
        $specializationIds = $data['specialization_ids'] ?? null;

        unset($data['specialization_ids']);

        // Upload new profile photo
        if (isset($data['profile_photo'])) {
            $oldProfilePhoto = $engineer->profile_photo_path;

            $data['profile_photo_path'] = $this->storageService->uploadPublic(
                $data['profile_photo'],
                "engineers/{$engineer->id}/profile-photos"
            );

            unset($data['profile_photo']);

            $this->storageService->deletePublic($oldProfilePhoto);
        }

        // Upload new CV
        if (isset($data['cv'])) {
            $oldCv = $engineer->cv_path;

            $data['cv_path'] = $this->storageService->uploadPublic(
                $data['cv'],
                "engineers/{$engineer->id}/cvs"
            );

            unset($data['cv']);

            $this->storageService->deletePublic($oldCv);
        }

        $engineer->update($data);

        if ($specializationIds !== null) {
            $engineer->specializations()->sync($specializationIds);
        }

        return $engineer->load([
            'user',
            'specializations',
            'certificates',
            'portfolioItems',
        ]);
    }
}