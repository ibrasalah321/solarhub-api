<?php

namespace App\Services\Store;

use App\Models\Store;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }



    /**
     * Update the authenticated store owner's own profile.
     * Approval status can only be changed via updateApproval().
     */
    public function updateMyStore(User $user, Store $store, array $data): Store
    {
        $this->ensureOwnership($user, $store);

        if (isset($data['commercial_file'])) {
            $this->storageService->deletePrivate($store->commercial_file_path);
            $data['commercial_file_path'] = $this->storageService->uploadPrivate(
                $data['commercial_file'],
                "stores/{$user->id}/documents"
            );
        }

        if (isset($data['company_logo'])) {
            $this->storageService->deletePublic($store->company_logo_path);
            $data['company_logo_path'] = $this->storageService->uploadPublic(
                $data['company_logo'],
                "stores/{$user->id}/logo"
            );
        }

        unset($data['commercial_file'], $data['company_logo']);

        $store->update($data);

        return $store;
    }



    private function ensureOwnership(User $user, Store $store): void
    {
        abort_unless(
            $store->user_id === $user->id,
            403,
            'You are not allowed to manage this store.'
        );
    }
}
