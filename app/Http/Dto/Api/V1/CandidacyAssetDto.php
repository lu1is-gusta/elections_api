<?php

namespace App\Http\Dto\Api\V1;

use App\Models\CandidacyAsset;

final readonly class CandidacyAssetDto
{
    /**
     * @param  array{
     *     id: int,
     *     tse_order: int|null,
     *     type: string|null,
     *     description: string|null,
     *     value: string|null
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(CandidacyAsset $asset): self
    {
        return new self([
            'id' => $asset->id,
            'tse_order' => $asset->tse_order,
            'type' => $asset->type,
            'description' => $asset->description,
            'value' => $asset->value,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     tse_order: int|null,
     *     type: string|null,
     *     description: string|null,
     *     value: string|null
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
