<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Admin\RejectProfessionalRequest;
use App\Http\Resources\Auth\Admin\AdminEngineerApprovalResource;
use App\Http\Resources\Auth\Admin\AdminStoreApprovalResource;
use App\Models\EngineerProfile;
use App\Models\Store;
use App\Services\Auth\Admin\AdminApprovalService;
use App\Services\Notification\DomainNotificationDispatcher;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AdminApprovalController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AdminApprovalService $adminApprovalService,
        private readonly DomainNotificationDispatcher $notifications
    ) {}

    public function pendingEngineers()
    {
        $engineers = $this->adminApprovalService->pendingEngineers();

        return $this->successResponse(
            AdminEngineerApprovalResource::collection($engineers),
            'Pending engineer applications retrieved successfully.'
        );
    }

    public function approveEngineer(Request $request, EngineerProfile $engineer)
    {
        $engineer = $this->adminApprovalService
            ->approveEngineer($engineer);

        $this->notifications->professionalDecision(
            $request->user(),
            $engineer->load('user'),
            true
        );

        return $this->successResponse(
            new AdminEngineerApprovalResource($engineer),
            'Engineer application approved successfully.'
        );
    }

    public function rejectEngineer(
        RejectProfessionalRequest $request,
        EngineerProfile $engineer
    ) {
        $data = $request->validated();

        $engineer = $this->adminApprovalService->rejectEngineer(
            $engineer,
            $data['rejection_reason']
        );

        $this->notifications->professionalDecision(
            $request->user(),
            $engineer->load('user'),
            false
        );

        return $this->successResponse(
            new AdminEngineerApprovalResource($engineer),
            'Engineer application rejected successfully.'
        );
    }

    public function pendingStores()
    {
        $stores = $this->adminApprovalService->pendingStores();

        return $this->successResponse(
            AdminStoreApprovalResource::collection($stores),
            'Pending store applications retrieved successfully.'
        );
    }

    public function approveStore(Request $request, Store $store)
    {
        $store = $this->adminApprovalService
            ->approveStore($store);

        $this->notifications->professionalDecision(
            $request->user(),
            $store->load('user'),
            true
        );

        return $this->successResponse(
            new AdminStoreApprovalResource($store),
            'Store application approved successfully.'
        );
    }

    public function rejectStore(
        RejectProfessionalRequest $request,
        Store $store
    ) {
        $data = $request->validated();

        $store = $this->adminApprovalService->rejectStore(
            $store,
            $data['rejection_reason']
        );

        $this->notifications->professionalDecision(
            $request->user(),
            $store->load('user'),
            false
        );

        return $this->successResponse(
            new AdminStoreApprovalResource($store),
            'Store application rejected successfully.'
        );
    }
}
