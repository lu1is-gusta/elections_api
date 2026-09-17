<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'number',
    'acronym',
    'name',
])]
class Party extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
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
