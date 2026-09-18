<?php

namespace App\Http\Dto\Api\V1;

use App\Models\Candidacy;
use App\Models\CandidacyAsset;
use App\Models\CandidacyLink;

final readonly class CandidacyShowDto
{
    /**
     * @param  array<string, mixed>  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(Candidacy $candidacy): self
    {
        $payload = [
            ...CandidacyCardDto::from($candidacy)->toArray(),
            'occupation' => $candidacy->occupation,
            'education' => $candidacy->education,
            'age_at_election' => $candidacy->age_at_election,
            'result' => $candidacy->result,
            'is_reelection' => $candidacy->is_reelection,
            'max_campaign_expense' => $candidacy->max_campaign_expense,
            'source_extracted_at' => $candidacy->source_extracted_at?->toIso8601String(),
        ];

        if ($candidacy->relationLoaded('detail')) {
            $payload['detail'] = $candidacy->detail === null
                ? null
                : CandidacyDetailDto::from($candidacy->detail)->toArray();
        }

        if ($candidacy->relationLoaded('assets')) {
            $payload['assets'] = $candidacy->assets
                ->map(fn (CandidacyAsset $asset): array => CandidacyAssetDto::from($asset)->toArray())
                ->values()
                ->all();
        }

        if ($candidacy->relationLoaded('links')) {
            $payload['links'] = $candidacy->links
                ->map(fn (CandidacyLink $link): array => CandidacyLinkDto::from($link)->toArray())
                ->values()
                ->all();
        }

        if ($candidacy->relationLoaded('person')) {
            $payload['person'] = $candidacy->person === null
                ? null
                : PersonSummaryDto::from($candidacy->person)->toArray();

            $payload['timeline'] = $candidacy->person === null
                ? []
                : $candidacy->person->candidacies
                    ->reject(fn (Candidacy $other): bool => $other->id === $candidacy->id)
                    ->map(fn (Candidacy $other): array => CandidacyCardDto::from($other)->toArray())
                    ->values()
                    ->all();
        }

        return new self($payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
