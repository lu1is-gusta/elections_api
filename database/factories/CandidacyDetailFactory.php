<?php

namespace Database\Factories;

use App\Models\Candidacy;
use App\Models\CandidacyDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidacyDetail>
 */
class CandidacyDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'candidacy_id' => Candidacy::factory(),
            'coalition_name' => 'Coligação Exemplo',
            'coalition_composition' => 'PT / PCDOB',
            'federation_acronym' => null,
            'nationality' => 'Brasileira',
            'marital_status' => 'Solteiro(a)',
            'process_number' => '123456',
            'replaced' => false,
            'extra' => [],
        ];
    }
}
