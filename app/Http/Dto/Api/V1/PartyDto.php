<?php

namespace App\Http\Dto\Api\V1;

use App\Models\Party;

final readonly class PartyDto
{
    /**
     * @param  array{
     *     id: int,
     *     number: int,
     *     acronym: string,
     *     name: string
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(Party $party): self
    {
        return new self([
            'id' => $party->id,
            'number' => $party->number,
            'acronym' => $party->acronym,
            'name' => $party->name,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     number: int,
     *     acronym: string,
     *     name: string
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
