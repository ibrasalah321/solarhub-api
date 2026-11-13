<?php

namespace App\Http\Controllers\Api\Enginner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Engineer\SpecializationResource;
use App\Models\Specialization;
use App\Services\Engineer\SpecializationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly SpecializationService $specializationService
    ) {}

    public function index()
    {
        $includeInactive = request()->boolean('all') || (bool) request()->user()?->hasRole('admin');

        $specializations = \App\Support\PublicCatalogCache::remember(
            \App\Support\PublicCatalogCache::SPECIALIZATIONS,
            fn () => SpecializationResource::collection(
                Specialization::query()
                    ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
                    ->orderBy('id', 'asc')
                    ->get()
            )->resolve(),
            $includeInactive ? 'all' : 'default'
        );

        return $this->successResponse(
            $specializations,
            'Specializations retrieved successfully.'
        );
    }

    public function show(Specialization $specialization)
    {
        return $this->successResponse(
            new SpecializationResource($specialization),
            'Specialization retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        $specialization = Specialization::create($validated);

        return $this->successResponse(
            new SpecializationResource($specialization),
            'تمت إضافة التخصص الهندسي بنجاح.',
            201
        );
    }

    public function update(Request $request, Specialization $specialization)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $specialization->update($validated);

        return $this->successResponse(
            new SpecializationResource($specialization),
            'تم تحديث التخصص الهندسي بنجاح.'
        );
    }

    public function destroy(Specialization $specialization)
    {
        abort_if(
            $specialization->engineerProfiles()->exists(),
            409,
            'تعذر حذف التخصص: هذا التخصص مرتبط بملفات مهندسين معتمدين في المنصة.'
        );

        $specialization->delete();

        return $this->successResponse(null, 'تم حذف التخصص بنجاح.');
    }
}
