<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tse_ue_code',
    'uf',
    'name',
    'kind',
    'ibge_code',
    'parent_id',
])]
class ElectoralUnit extends Model
{
    public $timestamps = false;

    /**
     * @return BelongsTo<ElectoralUnit, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ElectoralUnit::class, 'parent_id');
    }

    /**
     * @return HasMany<ElectoralUnit, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(ElectoralUnit::class, 'parent_id');
    }

    /**
     * @return HasMany<Candidacy, $this>
     */
    public function candidacies(): HasMany
    {
        return $this->hasMany(Candidacy::class);
    }
}
