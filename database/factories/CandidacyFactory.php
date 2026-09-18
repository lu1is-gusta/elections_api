<?php

namespace Database\Factories;

use App\Models\Candidacy;
use App\Models\Election;
use App\Models\ElectoralUnit;
use App\Models\Office;
use App\Models\Party;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidacy>
 */
class CandidacyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'election_id' => Election::factory(),
            'office_id' => Office::factory(),
            'electoral_unit_id' => ElectoralUnit::factory(),
            'party_id' => Party::factory(),
            'tse_sq_candidato' => fake()->unique()->numberBetween(1, 2_000_000_000),
            'turn' => 1,
            'ballot_number' => fake()->numberBetween(10, 99_999),
            'ballot_name' => strtoupper(fake()->firstName()),
            'civil_name' => fake()->name(),
            'uf' => 'SP',
            'unit_name' => 'São Paulo',
            'office_name' => 'Prefeito',
            'party_acronym' => 'PT',
            'party_number' => 13,
            'status' => 'apto',
            'is_elected' => false,
            'declared_assets' => false,
            'source_extracted_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Candidacy $candidacy): void {
            if ($candidacy->office !== null) {
                $candidacy->office_name = $candidacy->office->name;
            }

            if ($candidacy->party !== null) {
                $candidacy->party_acronym = $candidacy->party->acronym;
                $candidacy->party_number = $candidacy->party->number;
            }
        });
    }
}
