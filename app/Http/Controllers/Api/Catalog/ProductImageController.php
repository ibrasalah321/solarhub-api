<?php

<<<<<<< HEAD
namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
=======
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductImageRequest;
use App\Http\Resources\Catalog\ProductImageResource;
use App\Models\MasterProduct;
use App\Models\ProductImage;
use App\Services\Catalog\ProductImageService;
use App\Traits\ApiResponseTrait;
>>>>>>> c98dc2f4472927f1264e999c5a4f3e686d8b5da0
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
<<<<<<< HEAD
    //
=======
    use ApiResponseTrait;

    public function __construct(
        private readonly ProductImageService $productImageService
    ) {
    }

    public function store(StoreProductImageRequest $request)
    {
        $product = MasterProduct::query()->findOrFail($request->validated('product_id'));

        $image = $this->productImageService->addImage(
            $product,
            $request->file('image'),
            (bool) $request->boolean('is_featured')
        );

        return $this->successResponse(
            new ProductImageResource($image),
            'Image uploaded successfully.',
            201
        );
    }

    public function markFeatured(Request $request, ProductImage $productImage)
    {
        $image = $this->productImageService->markFeatured($productImage);

        return $this->successResponse(
            new ProductImageResource($image),
            'Image marked as featured successfully.'
        );
    }

    public function destroy(ProductImage $productImage)
    {
        $this->productImageService->deleteImage($productImage);

        return $this->successResponse(null, 'Image deleted successfully.');
    }
>>>>>>> c98dc2f4472927f1264e999c5a4f3e686d8b5da0
}
