<?php

namespace App\Http\Responses\Api\V1;

use App\Http\Dto\Api\V1\OfficeDto;
use App\Models\Office;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class OfficeListResponse implements Responsable
{
    /**
     * @param  Collection<int, Office>  $offices
     */
    public function __construct(private Collection $offices) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => $this->offices
                ->map(fn (Office $office): array => OfficeDto::from($office)->toArray())
                ->values()
                ->all(),
        ]);
    }
}
