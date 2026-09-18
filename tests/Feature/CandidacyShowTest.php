<?php

namespace Tests\Feature;

use App\Models\Candidacy;
use App\Models\CandidacyAsset;
use App\Models\CandidacyDetail;
use App\Models\CandidacyLink;
use App\Models\Person;
use App\Models\PersonIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidacyShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_candidacy_ficha(): void
    {
        $candidacy = Candidacy::factory()->create([
            'ballot_name' => 'SILVINHO',
            'occupation' => 'Advogado',
        ]);
        CandidacyDetail::factory()->create([
            'candidacy_id' => $candidacy->id,
            'coalition_name' => 'Coligação Exemplo',
        ]);
        CandidacyAsset::factory()->create([
            'candidacy_id' => $candidacy->id,
            'description' => 'Apartamento',
        ]);
        CandidacyLink::factory()->create([
            'candidacy_id' => $candidacy->id,
            'kind' => 'instagram',
            'url' => 'https://instagram.com/silvinho',
        ]);

        $response = $this->getJson('/api/v1/candidacies/'.$candidacy->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $candidacy->id)
            ->assertJsonPath('data.ballot_name', 'SILVINHO')
            ->assertJsonPath('data.occupation', 'Advogado')
            ->assertJsonPath('data.person.id', $candidacy->person_id)
            ->assertJsonPath('data.detail.coalition_name', 'Coligação Exemplo')
            ->assertJsonPath('data.assets.0.description', 'Apartamento')
            ->assertJsonPath('data.links.0.url', 'https://instagram.com/silvinho');
    }

    public function test_it_includes_other_candidacies_of_the_same_person(): void
    {
        $person = Person::factory()->create();
        $previous = Candidacy::factory()->create([
            'person_id' => $person->id,
        ]);
        $current = Candidacy::factory()->create([
            'person_id' => $person->id,
        ]);

        $response = $this->getJson('/api/v1/candidacies/'.$current->id);

        $response->assertOk()
            ->assertJsonPath('data.id', $current->id)
            ->assertJsonCount(1, 'data.timeline')
            ->assertJsonPath('data.timeline.0.id', $previous->id);
    }

    public function test_it_does_not_expose_cpf_or_voter_id(): void
    {
        $person = Person::factory()->create();
        PersonIdentifier::query()->create([
            'person_id' => $person->id,
            'cpf' => '12345678901',
            'voter_id' => '123456789012',
        ]);
        $candidacy = Candidacy::factory()->create([
            'person_id' => $person->id,
        ]);

        $response = $this->getJson('/api/v1/candidacies/'.$candidacy->id);

        $response->assertOk()
            ->assertJsonMissing(['cpf' => '12345678901'])
            ->assertJsonMissing(['voter_id' => '123456789012'])
            ->assertJsonMissingPath('data.person.identifier')
            ->assertJsonMissingPath('data.person.cpf');
    }

    public function test_it_returns_not_found_for_an_unknown_candidacy(): void
    {
        $this->getJson('/api/v1/candidacies/999')
            ->assertNotFound();
    }
}
