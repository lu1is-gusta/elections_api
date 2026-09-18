<?php

namespace Tests\Feature;

use App\Models\Candidacy;
use App\Models\CandidacyDetail;
use App\Models\Election;
use App\Models\Office;
use App\Models\Party;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidacyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requires_an_election_id(): void
    {
        $this->getJson('/api/v1/candidacies')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['election_id']);
    }

    public function test_it_lists_candidacies_for_an_election(): void
    {
        $election = Election::factory()->create();
        $candidacy = Candidacy::factory()->create([
            'election_id' => $election->id,
            'ballot_name' => 'SILVINHO',
        ]);
        Candidacy::factory()->create();

        $response = $this->getJson('/api/v1/candidacies?election_id='.$election->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $candidacy->id)
            ->assertJsonPath('data.0.ballot_name', 'SILVINHO')
            ->assertJsonPath('data.0.person_id', $candidacy->person_id)
            ->assertJsonMissingPath('data.0.detail')
            ->assertJsonMissingPath('data.0.assets')
            ->assertJsonMissingPath('data.0.links')
            ->assertJsonMissingPath('data.0.timeline');
    }

    public function test_it_does_not_include_coalition_details_on_the_card(): void
    {
        $election = Election::factory()->create();
        $candidacy = Candidacy::factory()->create([
            'election_id' => $election->id,
        ]);
        CandidacyDetail::factory()->create([
            'candidacy_id' => $candidacy->id,
            'coalition_name' => 'Coligação Secreta',
        ]);

        $response = $this->getJson('/api/v1/candidacies?election_id='.$election->id);

        $response->assertOk()
            ->assertJsonMissing(['coalition_name' => 'Coligação Secreta'])
            ->assertJsonMissingPath('data.0.detail');
    }

    public function test_it_applies_filters_together(): void
    {
        $election = Election::factory()->create();
        $office = Office::factory()->create(['name' => 'Prefeito']);
        $party = Party::factory()->create(['acronym' => 'PT']);
        $matching = Candidacy::factory()->create([
            'election_id' => $election->id,
            'office_id' => $office->id,
            'party_id' => $party->id,
            'uf' => 'SP',
            'is_elected' => true,
        ]);
        Candidacy::factory()->create([
            'election_id' => $election->id,
            'office_id' => $office->id,
            'party_id' => $party->id,
            'uf' => 'RJ',
            'is_elected' => true,
        ]);
        Candidacy::factory()->create([
            'election_id' => $election->id,
            'office_id' => $office->id,
            'party_id' => $party->id,
            'uf' => 'SP',
            'is_elected' => false,
        ]);

        $response = $this->getJson('/api/v1/candidacies?election_id='.$election->id.'&uf=SP&office_id='.$office->id.'&party_id='.$party->id.'&elected=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
    }

    public function test_it_searches_by_ballot_name(): void
    {
        $election = Election::factory()->create();
        $matching = Candidacy::factory()->create([
            'election_id' => $election->id,
            'ballot_name' => 'SILVINHO',
        ]);
        Candidacy::factory()->create([
            'election_id' => $election->id,
            'ballot_name' => 'MARIA',
        ]);

        $response = $this->getJson('/api/v1/candidacies?election_id='.$election->id.'&q=sil');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
    }

    public function test_it_rejects_a_short_search_term(): void
    {
        $election = Election::factory()->create();

        $this->getJson('/api/v1/candidacies?election_id='.$election->id.'&q=si')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['q']);
    }

    public function test_it_includes_source_extracted_at_on_the_list(): void
    {
        $election = Election::factory()->create();
        Candidacy::factory()->create([
            'election_id' => $election->id,
            'source_extracted_at' => '2026-09-16T03:00:00-03:00',
        ]);

        $this->getJson('/api/v1/candidacies?election_id='.$election->id)
            ->assertOk()
            ->assertJsonPath('next_cursor', null)
            ->assertJsonStructure(['data', 'next_cursor', 'source_extracted_at']);
    }
}
