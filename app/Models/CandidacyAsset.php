<?php

namespace App\Models;

use Database\Factories\CandidacyAssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'candidacy_id',
    'tse_order',
    'type',
    'description',
    'value',
])]
class CandidacyAsset extends Model
{
    /** @use HasFactory<CandidacyAssetFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tse_order' => 'integer',
            'value' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Candidacy, $this>
     */
    public function candidacy(): BelongsTo
    {
        return $this->belongsTo(Candidacy::class);
    }
}
