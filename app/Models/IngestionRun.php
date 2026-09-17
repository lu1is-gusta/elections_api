<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'source',
    'dataset',
    'file_name',
    'started_at',
    'finished_at',
    'status',
    'rows_upserted',
    'error',
])]
class IngestionRun extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'rows_upserted' => 'integer',
        ];
    }
}
