<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'civil_name' => fake()->name(),
            'ballot_name' => strtoupper(fake()->firstName()),
            'birth_date' => fake()->date(),
            'birth_uf' => 'SP',
            'birth_city' => fake()->city(),
            'gender_code' => 2,
            'race_code' => 1,
        ];
    }
}
