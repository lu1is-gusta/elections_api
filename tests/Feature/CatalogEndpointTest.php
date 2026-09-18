<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\ElectoralUnit;
use App\Models\Office;
use App\Models\Party;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CatalogEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_it_lists_elections(): void
    {
        $older = Election::factory()->create([
            'year' => 2020,
            'name' => 'Eleições 2020',
        ]);
        $newer = Election::factory()->create([
            'year' => 2024,
            'name' => 'Eleições 2024',
        ]);

        $response = $this->getJson('/api/v1/elections');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonPath('data.0.scope', 'municipal');
    }

    public function test_it_filters_offices_by_sphere(): void
    {
        $prefeito = Office::factory()->create([
            'name' => 'Prefeito',
            'sphere' => 'municipal',
        ]);
        Office::factory()->create([
            'name' => 'Presidente',
            'sphere' => 'federal',
        ]);

        $response = $this->getJson('/api/v1/offices?sphere=municipal');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $prefeito->id)
            ->assertJsonPath('data.0.sphere', 'municipal');
    }

    public function test_it_rejects_invalid_office_sphere(): void
    {
        $this->getJson('/api/v1/offices?sphere=lunar')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sphere']);
    }

    public function test_it_lists_parties(): void
    {
        $pt = Party::factory()->create([
            'number' => 13,
            'acronym' => 'PT',
            'name' => 'Partido dos Trabalhadores',
        ]);
        $mdb = Party::factory()->create([
            'number' => 15,
            'acronym' => 'MDB',
            'name' => 'Movimento Democrático Brasileiro',
        ]);

        $response = $this->getJson('/api/v1/parties');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $pt->id)
            ->assertJsonPath('data.1.id', $mdb->id);
    }

    public function test_it_lists_electoral_units_by_kind(): void
    {
        $saoPaulo = ElectoralUnit::factory()->create([
            'kind' => 'uf',
            'uf' => 'SP',
            'name' => 'São Paulo',
        ]);
        ElectoralUnit::factory()->municipio($saoPaulo)->create([
            'name' => 'Campinas',
        ]);

        $response = $this->getJson('/api/v1/electoral-units?kind=uf');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $saoPaulo->id)
            ->assertJsonPath('data.0.kind', 'uf');
    }

    public function test_it_lists_municipalities_for_a_uf(): void
    {
        $saoPaulo = ElectoralUnit::factory()->create([
            'kind' => 'uf',
            'uf' => 'SP',
            'name' => 'São Paulo',
        ]);
        $campinas = ElectoralUnit::factory()->municipio($saoPaulo)->create([
            'name' => 'Campinas',
        ]);
        ElectoralUnit::factory()->create([
            'kind' => 'uf',
            'uf' => 'RJ',
            'name' => 'Rio de Janeiro',
            'tse_ue_code' => 'RJ',
        ]);

        $response = $this->getJson('/api/v1/electoral-units?kind=municipio&uf=SP');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $campinas->id)
            ->assertJsonPath('data.0.name', 'Campinas');
    }

    public function test_it_requires_kind_on_electoral_units(): void
    {
        $this->getJson('/api/v1/electoral-units')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['kind']);
    }

    public function test_it_requires_uf_or_parent_when_listing_municipalities(): void
    {
        $this->getJson('/api/v1/electoral-units?kind=municipio')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['uf']);
    }
}
