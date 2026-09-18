<?php

namespace App\Models;

use Database\Factories\CandidacyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'person_id',
    'election_id',
    'office_id',
    'electoral_unit_id',
    'party_id',
    'tse_sq_candidato',
    'turn',
    'ballot_number',
    'ballot_name',
    'civil_name',
    'uf',
    'unit_name',
    'office_name',
    'party_acronym',
    'party_number',
    'status_code',
    'status',
    'result_code',
    'result',
    'is_elected',
    'is_reelection',
    'inserted_on_ballot',
    'occupation',
    'education',
    'age_at_election',
    'gender_code',
    'race_code',
    'photo_url',
    'max_campaign_expense',
    'declared_assets',
    'source_extracted_at',
])]
class Candidacy extends Model
{
    /** @use HasFactory<CandidacyFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tse_sq_candidato' => 'integer',
            'turn' => 'integer',
            'ballot_number' => 'integer',
            'party_number' => 'integer',
            'status_code' => 'integer',
            'result_code' => 'integer',
            'is_elected' => 'boolean',
            'is_reelection' => 'boolean',
            'inserted_on_ballot' => 'boolean',
            'age_at_election' => 'integer',
            'gender_code' => 'integer',
            'race_code' => 'integer',
            'max_campaign_expense' => 'decimal:2',
            'declared_assets' => 'boolean',
            'source_extracted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * @return BelongsTo<Election, $this>
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * @return BelongsTo<ElectoralUnit, $this>
     */
    public function electoralUnit(): BelongsTo
    {
        return $this->belongsTo(ElectoralUnit::class);
    }

    /**
     * @return BelongsTo<Party, $this>
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * @param  Builder<Candidacy>  $query
     * @param  array{
     *     election_id: int,
     *     uf?: string|null,
     *     office_id?: int|null,
     *     party_id?: int|null,
     *     electoral_unit_id?: int|null,
     *     q?: string|null,
     *     elected?: bool|null
     * }  $filters
     */
    public function scopeForListing(Builder $query, array $filters): void
    {
        $query->where('election_id', $filters['election_id'])
            ->when($filters['uf'] ?? null, fn (Builder $query, string $uf) => $query->where('uf', $uf))
            ->when($filters['office_id'] ?? null, fn (Builder $query, int|string $officeId) => $query->where('office_id', $officeId))
            ->when($filters['party_id'] ?? null, fn (Builder $query, int|string $partyId) => $query->where('party_id', $partyId))
            ->when($filters['electoral_unit_id'] ?? null, fn (Builder $query, int|string $unitId) => $query->where('electoral_unit_id', $unitId))
            ->when(array_key_exists('elected', $filters), fn (Builder $query) => $query->where('is_elected', $filters['elected']))
            ->when($filters['q'] ?? null, function (Builder $query, string $q): void {
                $operator = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $term = '%'.$q.'%';
                $query->where(function (Builder $query) use ($operator, $term): void {
                    $query->where('ballot_name', $operator, $term)
                        ->orWhere('civil_name', $operator, $term);
                });
            });
    }

    /**
     * @return HasOne<CandidacyDetail, $this>
     */
    public function detail(): HasOne
    {
        return $this->hasOne(CandidacyDetail::class);
    }

    /**
     * @return HasMany<CandidacyAsset, $this>
     */
    public function assets(): HasMany
    {
        return $this->hasMany(CandidacyAsset::class);
    }

    /**
     * @return HasMany<CandidacyLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(CandidacyLink::class);
    }
}
