<?php

namespace Database\Factories;

use App\Models\ElectoralUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectoralUnit>
 */
class ElectoralUnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tse_ue_code' => (string) fake()->unique()->numberBetween(10000, 99999),
            'uf' => 'SP',
            'name' => 'São Paulo',
            'kind' => 'uf',
            'ibge_code' => null,
            'parent_id' => null,
        ];
    }

    public function municipio(?ElectoralUnit $parent = null): static
    {
        return $this->state(function () use ($parent): array {
            $parent ??= ElectoralUnit::factory()->create();

            return [
                'kind' => 'municipio',
                'uf' => $parent->uf,
                'parent_id' => $parent->id,
                'name' => fake()->city(),
            ];
        });
    }
}
