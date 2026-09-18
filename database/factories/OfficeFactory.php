<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tse_code' => fake()->unique()->numberBetween(1, 32_000),
            'name' => 'Prefeito',
            'sphere' => 'municipal',
        ];
    }
}
