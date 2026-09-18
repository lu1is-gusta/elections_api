<?php

namespace App\Http\Dto\Api\V1;

use App\Models\CandidacyDetail;

final readonly class CandidacyDetailDto
{
    /**
     * @param  array{
     *     coalition_name: string|null,
     *     coalition_composition: string|null,
     *     federation_acronym: string|null,
     *     nationality: string|null,
     *     marital_status: string|null,
     *     process_number: string|null,
     *     replaced: bool|null,
     *     extra: array<string, mixed>
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(CandidacyDetail $detail): self
    {
        return new self([
            'coalition_name' => $detail->coalition_name,
            'coalition_composition' => $detail->coalition_composition,
            'federation_acronym' => $detail->federation_acronym,
            'nationality' => $detail->nationality,
            'marital_status' => $detail->marital_status,
            'process_number' => $detail->process_number,
            'replaced' => $detail->replaced,
            'extra' => $detail->extra ?? [],
        ]);
    }

    /**
     * @return array{
     *     coalition_name: string|null,
     *     coalition_composition: string|null,
     *     federation_acronym: string|null,
     *     nationality: string|null,
     *     marital_status: string|null,
     *     process_number: string|null,
     *     replaced: bool|null,
     *     extra: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
