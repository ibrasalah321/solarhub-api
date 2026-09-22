<?php

namespace App\Services\Engineer;

use App\Models\EngineerProfile;
use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Collection;
use App\Services\SupabaseStorageService;

class PortfolioItemService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function getEngineerPortfolio(EngineerProfile $engineer): Collection {
        return $engineer->portfolioItems()
            ->with([
                'serviceType',
                'governorate',
            ])
            ->latest()
            ->get();
    }

    public function create(EngineerProfile $engineer,array $data): PortfolioItem {

        if (isset($data['image'])) {
            $data['image_path'] = $this->storageService->uploadPublic(
                $data['image'],
                "engineers/{$engineer->id}/portfolio/images"
            );

            unset($data['image']);
        }

        if (isset($data['file'])) {
            $data['file_path'] = $this->storageService->uploadPrivate(
                $data['file'],
                "engineers/{$engineer->id}/portfolio/files"
            );

            unset($data['file']);
        }

        $portfolioItem = $engineer->portfolioItems()->create($data);

        return $portfolioItem->load([
            'serviceType',
            'governorate',
        ]);
    }
    public function update(EngineerProfile $engineer,PortfolioItem $portfolioItem,array $data): PortfolioItem {

        $this->ensureOwnership($engineer, $portfolioItem);

        if (isset($data['image'])) {
            $oldImage = $portfolioItem->image_path;

            $data['image_path'] = $this->storageService->uploadPublic(
                $data['image'],
                "engineers/{$engineer->id}/portfolio/images"
            );

            unset($data['image']);

            $this->storageService->deletePublic($oldImage);
        }

        if (isset($data['file'])) {
            $oldFile = $portfolioItem->file_path;

            $data['file_path'] = $this->storageService->uploadPrivate(
                $data['file'],
                "engineers/{$engineer->id}/portfolio/files"
            );

            unset($data['file']);

            $this->storageService->deletePrivate($oldFile);
        }

        $portfolioItem->update($data);

        return $portfolioItem->load([
            'serviceType',
            'governorate',
        ]);
    }

    public function delete(EngineerProfile $engineer,PortfolioItem $portfolioItem): void {

        $this->ensureOwnership(
            $engineer,
            $portfolioItem
        );

        $imagePath = $portfolioItem->image_path;
        $filePath = $portfolioItem->file_path;

        if ($imagePath) {
            $this->storageService->deletePublic($imagePath);
        }

        if ($filePath) {
            $this->storageService->deletePrivate($filePath);
        }

        $portfolioItem->delete();
    }

    public function getPublicPortfolio(int $engineerId,int $perPage = 10) {
        $engineer = EngineerProfile::query()
            ->where('approval_status', 'approved')
            ->findOrFail($engineerId);

        return $engineer->portfolioItems()
            ->with([
                'serviceType',
                'governorate',
            ])
            ->latest()
            ->paginate($perPage);
    }

    private function ensureOwnership(EngineerProfile $engineer,PortfolioItem $portfolioItem): void {
        abort_unless(
            $portfolioItem->engineer_id === $engineer->id,
            403,
            'You are not allowed to manage this portfolio item.'
        );
    }
}