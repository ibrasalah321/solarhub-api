<?php

<<<<<<< HEAD
namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductSpecificationController extends Controller
{
    //
=======
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreProductSpecificationRequest;
use App\Http\Requests\Catalog\UpdateProductSpecificationRequest;
use App\Http\Resources\Catalog\ProductSpecificationResource;
use App\Models\ProductSpecification;
use App\Traits\ApiResponseTrait;

class ProductSpecificationController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreProductSpecificationRequest $request)
    {
        $specification = ProductSpecification::create($request->validated());

        return $this->successResponse(
            new ProductSpecificationResource($specification),
            'Specification added successfully.',
            201
        );
    }

    public function update(UpdateProductSpecificationRequest $request, ProductSpecification $productSpecification)
    {
        $productSpecification->update($request->validated());

        return $this->successResponse(
            new ProductSpecificationResource($productSpecification),
            'Specification updated successfully.'
        );
    }

    public function destroy(ProductSpecification $productSpecification)
    {
        $productSpecification->delete();

        return $this->successResponse(null, 'Specification removed successfully.');
    }
>>>>>>> c98dc2f4472927f1264e999c5a4f3e686d8b5da0
}
