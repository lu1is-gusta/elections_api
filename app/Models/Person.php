<?php

namespace App\Models;

use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'civil_name',
    'ballot_name',
    'birth_date',
    'birth_uf',
    'birth_city',
    'gender_code',
    'race_code',
])]
class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'gender_code' => 'integer',
            'race_code' => 'integer',
        ];
    }

    /**
     * @return HasOne<PersonIdentifier, $this>
     */
    public function identifier(): HasOne
    {
        return $this->hasOne(PersonIdentifier::class);
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

    /**
     * @return HasMany<LegislativeActivity, $this>
     */
    public function legislativeActivities(): HasMany
    {
        return $this->hasMany(LegislativeActivity::class);
    }
}
