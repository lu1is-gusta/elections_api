<?php

namespace App\Models;

use Database\Factories\CandidacyDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'candidacy_id',
    'coalition_name',
    'coalition_composition',
    'federation_acronym',
    'nationality',
    'marital_status',
    'process_number',
    'replaced',
    'replaced_sq',
    'extra',
])]
class CandidacyDetail extends Model
{
    /** @use HasFactory<CandidacyDetailFactory> */
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'candidacy_id';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'replaced' => 'boolean',
            'replaced_sq' => 'integer',
            'extra' => 'array',
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
