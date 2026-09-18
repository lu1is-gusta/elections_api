<?php

namespace App\Models;

use Database\Factories\CandidacyLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'candidacy_id',
    'kind',
    'url',
])]
class CandidacyLink extends Model
{
    /** @use HasFactory<CandidacyLinkFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return BelongsTo<Candidacy, $this>
     */
    public function candidacy(): BelongsTo
    {
        return $this->belongsTo(Candidacy::class);
    }
}
