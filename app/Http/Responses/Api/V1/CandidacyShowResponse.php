<?php

namespace App\Http\Responses\Api\V1;

use App\Http\Dto\Api\V1\CandidacyShowDto;
use App\Models\Candidacy;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

final class CandidacyShowResponse implements Responsable
{
    public function __construct(private Candidacy $candidacy) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'data' => CandidacyShowDto::from($this->candidacy)->toArray(),
        ]);
    }
}
