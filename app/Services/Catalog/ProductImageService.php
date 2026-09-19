<?php

namespace App\Services\Catalog;

use App\Models\MasterProduct;
use App\Models\ProductImage;
use App\Services\SupabaseStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductImageService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    /**
     * Upload a new image for a product. If marked as featured (or it is the
     * product's first image), every other image of that product is un-featured.
     */
    public function addImage(MasterProduct $product, UploadedFile $image, bool $isFeatured = false): ProductImage
    {
        return DB::transaction(function () use ($product, $image, $isFeatured) {
            $isFirstImage = ! $product->images()->exists();

            $path = $this->storageService->uploadPublic(
                $image,
                "products/{$product->id}/images"
            );

            $productImage = $product->images()->create([
                'image_path' => $path,
                'is_featured' => $isFirstImage ? true : $isFeatured,
            ]);

            if ($productImage->is_featured) {
                $this->clearOtherFeatured($product, $productImage);
            }

            return $productImage;
        });
    }

    /**
     * Mark an image as the featured one for its product.
     */
    public function markFeatured(ProductImage $image): ProductImage
    {
        return DB::transaction(function () use ($image) {
            $image->update(['is_featured' => true]);

            $this->clearOtherFeatured($image->masterProduct, $image);

            return $image;
        });
    }

    /**
     * Delete a product image, promoting another one to featured if needed.
     */
    public function deleteImage(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            $wasFeatured = $image->is_featured;
            $product = $image->masterProduct;

            $this->storageService->deletePublic($image->image_path);
            $image->delete();

            if ($wasFeatured) {
                $product->images()->first()?->update(['is_featured' => true]);
            }
        });
    }

    private function clearOtherFeatured(MasterProduct $product, ProductImage $image): void
    {
        $product->images()
            ->where('id', '!=', $image->id)
            ->update(['is_featured' => false]);
    }
}
