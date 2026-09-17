<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'person_id',
    'mandate_id',
    'kind',
    'occurred_at',
    'title',
    'summary',
    'url',
    'metadata',
    'source',
])]
class LegislativeActivity extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'metadata' => 'array',
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
     * @return BelongsTo<PoliticalMandate, $this>
     */
    public function mandate(): BelongsTo
    {
        return $this->belongsTo(PoliticalMandate::class, 'mandate_id');
    }
}
