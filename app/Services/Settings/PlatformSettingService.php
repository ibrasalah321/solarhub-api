<?php

namespace App\Services\Settings;

use App\Models\PlatformSetting;
use App\Services\SupabaseStorageService;

class PlatformSettingService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    /**
     * Get the platform settings, creating the singleton row with sane
     * defaults on first access if it doesn't exist yet.
     */
    public function get(): PlatformSetting
    {
        return PlatformSetting::query()->firstOrCreate([], [
            'platform_name' => 'SolarHub',
            'support_phone' => '',
            'support_email' => 'support@example.com',
            'logo_path' => '',
            'currency' => 'YER',
            'is_maintenance_mode' => false,
        ]);
    }

    /**
     * Update the platform settings.
     */
    public function update(array $data): PlatformSetting
    {
        $settings = $this->get();

        if (isset($data['logo'])) {
            $this->storageService->deletePublic($settings->logo_path);
            $data['logo_path'] = $this->storageService->uploadPublic($data['logo'], 'platform');
        }

        if (isset($data['favicon'])) {
            $this->storageService->deletePublic($settings->favicon_path);
            $data['favicon_path'] = $this->storageService->uploadPublic($data['favicon'], 'platform');
        }

        unset($data['logo'], $data['favicon']);

        $settings->update($data);

        return $settings;
    }
}
