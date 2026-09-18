<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ElectoralUnitRequest;
use App\Http\Responses\Api\V1\ElectoralUnitListResponse;
use App\Models\ElectoralUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ElectoralUnitController extends Controller
{
    public function index(ElectoralUnitRequest $request): ElectoralUnitListResponse
    {
        $filters = $request->safe()->only(['kind', 'uf', 'parent_id', 'q']);
        $cacheKey = 'electoral-units:index:'.md5((string) json_encode($filters));

        $units = Cache::remember($cacheKey, 3600, function () use ($filters) {
            return ElectoralUnit::query()
                ->where('kind', $filters['kind'])
                ->when($filters['uf'] ?? null, fn (Builder $query, string $uf) => $query->where('uf', $uf))
                ->when($filters['parent_id'] ?? null, fn (Builder $query, int|string $parentId) => $query->where('parent_id', $parentId))
                ->when($filters['q'] ?? null, function (Builder $query, string $q): void {
                    $operator = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
                    $query->where('name', $operator, '%'.$q.'%');
                })
                ->orderBy('name')
                ->orderBy('id')
                ->get();
        });

        return new ElectoralUnitListResponse($units);
    }
}
