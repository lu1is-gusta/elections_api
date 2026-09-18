<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\Api\V1\ElectionListResponse;
use App\Models\Election;
use Illuminate\Support\Facades\Cache;

class ElectionController extends Controller
{
    public function index(): ElectionListResponse
    {
        $elections = Cache::remember('elections:index', 3600, function () {
            return Election::query()
                ->orderByDesc('year')
                ->orderBy('id')
                ->get();
        });

        return new ElectionListResponse($elections);
    }
}
