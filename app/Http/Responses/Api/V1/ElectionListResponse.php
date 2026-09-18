<?php

namespace App\Http\Responses\Api\V1;

use App\Http\Dto\Api\V1\ElectionDto;
use App\Models\Election;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class ElectionListResponse implements Responsable
{
    /**
     * @param  Collection<int, Election>  $elections
     */
    public function __construct(private Collection $elections) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => $this->elections
                ->map(fn (Election $election): array => ElectionDto::from($election)->toArray())
                ->values()
                ->all(),
        ]);
    }
}
