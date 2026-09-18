<?php

namespace App\Http\Responses\Api\V1;

use App\Http\Dto\Api\V1\CandidacyCardDto;
use App\Models\Candidacy;
use DateTimeInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

final class CandidacyListResponse implements Responsable
{
    /**
     * @param  CursorPaginator<int, Candidacy>  $candidacies
     */
    public function __construct(private CursorPaginator $candidacies) {}

    public function toResponse($request): JsonResponse
    {
        $sourceExtractedAt = $this->candidacies
            ->getCollection()
            ->max('source_extracted_at');

        return response()->json([
            'data' => $this->candidacies
                ->getCollection()
                ->map(fn (Candidacy $candidacy): array => CandidacyCardDto::from($candidacy)->toArray())
                ->values()
                ->all(),
            'next_cursor' => $this->candidacies->nextCursor()?->encode(),
            'source_extracted_at' => $sourceExtractedAt instanceof DateTimeInterface
                ? $sourceExtractedAt->format(DateTimeInterface::ATOM)
                : $sourceExtractedAt,
        ]);
    }
}
