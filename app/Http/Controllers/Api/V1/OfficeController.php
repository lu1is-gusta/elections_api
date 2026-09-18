<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\OfficeRequest;
use App\Http\Responses\Api\V1\OfficeListResponse;
use App\Models\Office;
use Illuminate\Support\Facades\Cache;

class OfficeController extends Controller
{
    public function index(OfficeRequest $request): OfficeListResponse
    {
        $sphere = $request->validated('sphere');

        $offices = Cache::remember('offices:index:'.($sphere ?? '*'), 3600, function () use ($sphere) {
            return Office::query()
                ->when($sphere, fn ($query, string $sphere) => $query->where('sphere', $sphere))
                ->orderBy('name')
                ->get();
        });

        return new OfficeListResponse($offices);
    }
}
