<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engineer\StoreEngineerCertificateRequest;
use App\Http\Resources\EngineerCertificateResource;
use App\Models\EngineerCertificate;
use App\Services\EngineerCertificateService;
use App\Services\EngineerProfileService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class EngineerCertificateController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly EngineerCertificateService $certificateService,
        private readonly EngineerProfileService $engineerProfileService
    ) {
    }

    /**
     * Display authenticated engineer certificates.
     */
    public function index(Request $request)
    {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $certificates = $this->certificateService
            ->getCertificates($engineer);

        return $this->successResponse(
            EngineerCertificateResource::collection($certificates),
            'Certificates retrieved successfully.'
        );
    }

    /**
     * Upload a new certificate.
     */
    public function store(StoreEngineerCertificateRequest $request)
    {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $certificate = $this->certificateService
            ->createCertificate(
                $engineer,
                $request->file('certificate')
            );

        return $this->successResponse(
            new EngineerCertificateResource($certificate),
            'Certificate uploaded successfully.',
            201
        );
    }

    /**
     * Delete engineer certificate.
     */
    public function destroy(Request $request,EngineerCertificate $certificate) {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $this->certificateService->deleteCertificate(
            $engineer,
            $certificate
        );

        return $this->successResponse(
            null,
            'Certificate deleted successfully.'
        );
    }
}