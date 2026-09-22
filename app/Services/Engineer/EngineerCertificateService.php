<?php

namespace App\Services\Engineer;

use App\Models\EngineerCertificate;
use App\Models\EngineerProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use App\Services\SupabaseStorageService;

class EngineerCertificateService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function getCertificates(EngineerProfile $engineer): Collection {
        return $engineer->certificates()
            ->latest()
            ->get();
    }

    public function createCertificate(EngineerProfile $engineer,UploadedFile $file): EngineerCertificate {
        $path = $this->storageService->uploadPrivate(
            $file,
            "engineers/{$engineer->id}/certificates"
        );

        return $engineer->certificates()->create([
            'file_path' => $path,
        ]);
    }

    public function deleteCertificate(EngineerProfile $engineer,EngineerCertificate $certificate): void {
        abort_unless(
            $certificate->engineer_id === $engineer->id,
            403,
            'You are not allowed to delete this certificate.'
        );

        $path = $certificate->file_path;

        $certificate->delete();

        $this->storageService->deletePrivate($path);
    }
}