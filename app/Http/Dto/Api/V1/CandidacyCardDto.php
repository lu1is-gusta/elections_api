<?php

namespace App\Http\Dto\Api\V1;

use App\Models\Candidacy;

final readonly class CandidacyCardDto
{
    /**
     * @param  array{
     *     id: int,
     *     person_id: int,
     *     ballot_number: int,
     *     ballot_name: string,
     *     civil_name: string,
     *     party_acronym: string,
     *     party_number: int,
     *     office_name: string,
     *     uf: string,
     *     unit_name: string,
     *     photo_url: string|null,
     *     is_elected: bool,
     *     declared_assets: bool|null,
     *     turn: int,
     *     status: string|null
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(Candidacy $candidacy): self
    {
        return new self([
            'id' => $candidacy->id,
            'person_id' => $candidacy->person_id,
            'ballot_number' => $candidacy->ballot_number,
            'ballot_name' => $candidacy->ballot_name,
            'civil_name' => $candidacy->civil_name,
            'party_acronym' => $candidacy->party_acronym,
            'party_number' => $candidacy->party_number,
            'office_name' => $candidacy->office_name,
            'uf' => $candidacy->uf,
            'unit_name' => $candidacy->unit_name,
            'photo_url' => $candidacy->photo_url,
            'is_elected' => $candidacy->is_elected,
            'declared_assets' => $candidacy->declared_assets,
            'turn' => $candidacy->turn,
            'status' => $candidacy->status,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     person_id: int,
     *     ballot_number: int,
     *     ballot_name: string,
     *     civil_name: string,
     *     party_acronym: string,
     *     party_number: int,
     *     office_name: string,
     *     uf: string,
     *     unit_name: string,
     *     photo_url: string|null,
     *     is_elected: bool,
     *     declared_assets: bool|null,
     *     turn: int,
     *     status: string|null
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
