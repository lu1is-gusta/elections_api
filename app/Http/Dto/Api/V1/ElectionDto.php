<?php

namespace App\Http\Dto\Api\V1;

use App\Models\Election;

final readonly class ElectionDto
{
    /**
     * @param  array{
     *     id: int,
     *     year: int,
     *     tse_election_id: int,
     *     name: string,
     *     kind: string,
     *     scope: string,
     *     election_date: string|null
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(Election $election): self
    {
        return new self([
            'id' => $election->id,
            'year' => $election->year,
            'tse_election_id' => $election->tse_election_id,
            'name' => $election->name,
            'kind' => $election->kind,
            'scope' => $election->scope,
            'election_date' => $election->election_date?->toDateString(),
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     year: int,
     *     tse_election_id: int,
     *     name: string,
     *     kind: string,
     *     scope: string,
     *     election_date: string|null
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
