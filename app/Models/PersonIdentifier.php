<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'person_id',
    'cpf',
    'voter_id',
])]
#[Hidden(['cpf', 'voter_id'])]
class PersonIdentifier extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'person_id';

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
