<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CandidacyRequest;
use App\Http\Responses\Api\V1\CandidacyListResponse;
use App\Http\Responses\Api\V1\CandidacyShowResponse;
use App\Models\Candidacy;

class CandidacyController extends Controller
{
    public function index(CandidacyRequest $request): CandidacyListResponse
    {
        $filters = $request->safe()->except(['cursor']);

        if (array_key_exists('elected', $filters)) {
            $filters['elected'] = $request->boolean('elected');
        }

        $candidacies = Candidacy::query()
            ->forListing($filters)
            ->orderBy('id')
            ->cursorPaginate(30)
            ->appends($filters);

        return new CandidacyListResponse($candidacies);
    }

    public function show(int $id): CandidacyShowResponse
    {
        $candidacy = Candidacy::query()->findOrFail($id);
        $candidacy->load([
            'detail',
            'assets' => fn ($query) => $query->orderBy('tse_order')->orderBy('id'),
            'links',
            'person',
            'person.candidacies' => fn ($query) => $query->orderByDesc('election_id')->orderByDesc('id'),
        ]);

        return new CandidacyShowResponse($candidacy);
    }
}
