<?php

namespace App\Http\Dto\Api\V1;

use App\Models\Office;

final readonly class OfficeDto
{
    /**
     * @param  array{
     *     id: int,
     *     tse_code: int,
     *     name: string,
     *     sphere: string
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(Office $office): self
    {
        return new self([
            'id' => $office->id,
            'tse_code' => $office->tse_code,
            'name' => $office->name,
            'sphere' => $office->sphere,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     tse_code: int,
     *     name: string,
     *     sphere: string
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
