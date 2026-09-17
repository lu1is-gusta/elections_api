<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'year',
    'tse_election_id',
    'name',
    'kind',
    'scope',
    'election_date',
])]
class Election extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'tse_election_id' => 'integer',
            'election_date' => 'date',
        ];
    }

    /**
     * @return HasMany<Candidacy, $this>
     */
    public function candidacies(): HasMany
    {
        return $this->hasMany(Candidacy::class);
    }
}
