<?php

namespace App\Http\Responses\Api\V1;

use App\Http\Dto\Api\V1\ElectoralUnitDto;
use App\Models\ElectoralUnit;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class ElectoralUnitListResponse implements Responsable
{
    /**
     * @param  Collection<int, ElectoralUnit>  $units
     */
    public function __construct(private Collection $units) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => $this->units
                ->map(fn (ElectoralUnit $unit): array => ElectoralUnitDto::from($unit)->toArray())
                ->values()
                ->all(),
        ]);
    }
}
