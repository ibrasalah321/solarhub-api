<?php

namespace App\Http\Controllers\Api\ServiceRequest;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRequest\ServiceTypeResource;
use App\Models\ServiceType;
use App\Services\ServiceRequest\ServiceTypeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ServiceTypeService $serviceTypeService
    ) {}

    public function index()
    {
        $includeInactive = request()->boolean('all') || (bool) request()->user()?->hasRole('admin');

        $serviceTypes = \App\Support\PublicCatalogCache::remember(
            \App\Support\PublicCatalogCache::SERVICE_TYPES,
            fn () => ServiceTypeResource::collection(
                ServiceType::query()
                    ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
                    ->orderBy('id', 'asc')
                    ->get()
            )->resolve(),
            $includeInactive ? 'all' : 'default'
        );

        return $this->successResponse(
            $serviceTypes,
            'Service types retrieved successfully.'
        );
    }

    public function show(ServiceType $serviceType)
    {
        return $this->successResponse(
            new ServiceTypeResource($serviceType),
            'Service type retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        if (!array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        $serviceType = ServiceType::create($validated);

        return $this->successResponse(
            new ServiceTypeResource($serviceType),
            'تمت إضافة باقة الخدمة الهندسية بنجاح.',
            201
        );
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $serviceType->update($validated);

        return $this->successResponse(
            new ServiceTypeResource($serviceType),
            'تم تحديث باقة الخدمة الهندسية بنجاح.'
        );
    }

    public function destroy(ServiceType $serviceType)
    {
        abort_if(
            $serviceType->serviceRequests()->exists() || $serviceType->portfolioItems()->exists(),
            409,
            'تعذر حذف باقة الخدمة: هذه الخدمة مرتبطة بطلبات خدمات أو أعمال سابقة في النظام.'
        );

        $serviceType->delete();

        return $this->successResponse(null, 'تم حذف باقة الخدمة الهندسية بنجاح.');
    }
}
