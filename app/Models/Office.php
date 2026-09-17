<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tse_code',
    'name',
    'sphere',
])]
class Office extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tse_code' => 'integer',
        ];
    }

    /**
     * @return HasMany<Candidacy, $this>
     */
    public function candidacies(): HasMany
    {
        return $this->hasMany(Candidacy::class);
    }

    /**
     * @return HasMany<PoliticalMandate, $this>
     */
    public function politicalMandates(): HasMany
    {
        return $this->hasMany(PoliticalMandate::class);
    }
}
