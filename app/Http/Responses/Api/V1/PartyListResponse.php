<?php

namespace App\Http\Responses\Api\V1;

use App\Http\Dto\Api\V1\PartyDto;
use App\Models\Party;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class PartyListResponse implements Responsable
{
    /**
     * @param  Collection<int, Party>  $parties
     */
    public function __construct(private Collection $parties) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => $this->parties
                ->map(fn (Party $party): array => PartyDto::from($party)->toArray())
                ->values()
                ->all(),
        ]);
    }
}
