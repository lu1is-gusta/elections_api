<?php

namespace Database\Factories;

use App\Models\Election;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Election>
 */
class ElectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->numberBetween(2016, 2026),
            'tse_election_id' => fake()->unique()->numberBetween(1, 99_999),
            'name' => 'Eleições Municipais 2024',
            'kind' => 'ordinaria',
            'scope' => 'municipal',
            'election_date' => '2024-10-06',
        ];
    }
}
