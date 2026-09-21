<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePlatformSettingRequest;
use App\Http\Resources\Settings\PlatformSettingResource;
use App\Services\Settings\PlatformSettingService;
use App\Traits\ApiResponseTrait;

class PlatformSettingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly PlatformSettingService $platformSettingService
    ) {
    }

    /**
     * Display the platform settings (public — used by client apps for
     * support info, currency, and maintenance mode).
     */
    public function show()
    {
        return $this->successResponse(
            new PlatformSettingResource($this->platformSettingService->get()),
            'Platform settings retrieved successfully.'
        );
    }

    /**
     * Update the platform settings.
     *
     * NOTE: intended for platform administrators. See the module-level note
     * regarding the current absence of a role/permission system.
     */
    public function update(UpdatePlatformSettingRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo');
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon');
        }

        $settings = $this->platformSettingService->update($data);

        return $this->successResponse(
            new PlatformSettingResource($settings),
            'Platform settings updated successfully.'
        );
    }
}
