<?php

namespace App\Http\Dto\Api\V1;

use App\Models\CandidacyLink;

final readonly class CandidacyLinkDto
{
    /**
     * @param  array{
     *     id: int,
     *     kind: string,
     *     url: string
     * }  $payload
     */
    private function __construct(private array $payload) {}

    public static function from(CandidacyLink $link): self
    {
        return new self([
            'id' => $link->id,
            'kind' => $link->kind,
            'url' => $link->url,
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     kind: string,
     *     url: string
     * }
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
