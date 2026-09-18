<?php

namespace App\Http\Dto\Api\V1;

use App\Models\ElectoralUnit;

final readonly class ElectoralUnitDto
{
    /**
     * @param  array{
     *     id: int,
     *     tse_ue_code: string,
     *     uf: string|null,
     *     name: string,
     *     kind: string,
     *     ibge_code: string|null,
     *     parent_id: int|null
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(ElectoralUnit $unit): self
    {
        return new self([
            'id' => $unit->id,
            'tse_ue_code' => $unit->tse_ue_code,
            'uf' => $unit->uf,
            'name' => $unit->name,
            'kind' => $unit->kind,
            'ibge_code' => $unit->ibge_code,
            'parent_id' => $unit->parent_id,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     tse_ue_code: string,
     *     uf: string|null,
     *     name: string,
     *     kind: string,
     *     ibge_code: string|null,
     *     parent_id: int|null
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
