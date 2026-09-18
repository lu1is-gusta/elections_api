<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\Api\V1\PartyListResponse;
use App\Models\Party;
use Illuminate\Support\Facades\Cache;

class PartyController extends Controller
{
    public function index(): PartyListResponse
    {
        $parties = Cache::remember('parties:index', 3600, function () {
            return Party::query()
                ->orderBy('number')
                ->orderBy('acronym')
                ->get();
        });

        return new PartyListResponse($parties);
    }
}
