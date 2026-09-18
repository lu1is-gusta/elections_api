<?php

namespace App\Http\Dto\Api\V1;

use App\Models\Person;

final readonly class PersonSummaryDto
{
    /**
     * @param  array{
     *     id: int,
     *     civil_name: string,
     *     ballot_name: string|null,
     *     birth_date: string|null,
     *     birth_uf: string|null,
     *     birth_city: string|null,
     *     gender_code: int|null,
     *     race_code: int|null
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(Person $person): self
    {
        return new self([
            'id' => $person->id,
            'civil_name' => $person->civil_name,
            'ballot_name' => $person->ballot_name,
            'birth_date' => $person->birth_date?->toDateString(),
            'birth_uf' => $person->birth_uf,
            'birth_city' => $person->birth_city,
            'gender_code' => $person->gender_code,
            'race_code' => $person->race_code,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     civil_name: string,
     *     ballot_name: string|null,
     *     birth_date: string|null,
     *     birth_uf: string|null,
     *     birth_city: string|null,
     *     gender_code: int|null,
     *     race_code: int|null
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
