<?php

namespace Database\Factories;

use App\Models\Candidacy;
use App\Models\CandidacyLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidacyLink>
 */
class CandidacyLinkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'candidacy_id' => Candidacy::factory(),
            'kind' => 'instagram',
            'url' => fake()->url(),
        ];
    }
}
