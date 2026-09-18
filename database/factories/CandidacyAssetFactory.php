<?php

namespace Database\Factories;

use App\Models\Candidacy;
use App\Models\CandidacyAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidacyAsset>
 */
class CandidacyAssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'candidacy_id' => Candidacy::factory(),
            'tse_order' => fake()->numberBetween(1, 20),
            'type' => 'Imóvel',
            'description' => 'Apartamento',
            'value' => '150000.00',
        ];
    }
}
