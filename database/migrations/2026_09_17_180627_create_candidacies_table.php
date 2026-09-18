<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidacies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('person_id')->constrained('people');
            $table->foreignId('election_id')->constrained('elections');
            $table->unsignedSmallInteger('office_id');
            $table->foreignId('electoral_unit_id')->constrained('electoral_units');
            $table->unsignedInteger('party_id');
            $table->unsignedBigInteger('tse_sq_candidato');
            $table->smallInteger('turn')->default(1);
            $table->integer('ballot_number');
            $table->text('ballot_name');
            $table->text('civil_name');
            $table->char('uf', 2);
            $table->text('unit_name');
            $table->text('office_name');
            $table->text('party_acronym');
            $table->smallInteger('party_number');
            $table->integer('status_code')->nullable();
            $table->text('status')->nullable();
            $table->integer('result_code')->nullable();
            $table->text('result')->nullable();
            $table->boolean('is_elected')->default(false);
            $table->boolean('is_reelection')->nullable();
            $table->boolean('inserted_on_ballot')->nullable();
            $table->text('occupation')->nullable();
            $table->text('education')->nullable();
            $table->smallInteger('age_at_election')->nullable();
            $table->smallInteger('gender_code')->nullable();
            $table->smallInteger('race_code')->nullable();
            $table->text('photo_url')->nullable();
            $table->decimal('max_campaign_expense', 15, 2)->nullable();
            $table->boolean('declared_assets')->nullable();
            $table->timestampTz('source_extracted_at');

            $table->foreign('office_id')->references('id')->on('offices');
            $table->foreign('party_id')->references('id')->on('parties');

            $table->unique(['election_id', 'tse_sq_candidato', 'turn']);
            $table->index(['election_id', 'uf', 'office_id', 'id'], 'candidacies_list_idx');
            $table->index(['election_id', 'electoral_unit_id', 'office_id', 'id'], 'candidacies_unit_idx');
            $table->index(['election_id', 'party_id', 'id'], 'candidacies_party_idx');
            $table->index(['election_id', 'ballot_number'], 'candidacies_number_idx');
        });

        DB::statement('CREATE INDEX candidacies_person_idx ON candidacies (person_id, election_id DESC)');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX candidacies_elected_idx ON candidacies (election_id, is_elected, id) WHERE is_elected = true');
            DB::statement('CREATE INDEX candidacies_name_trgm_idx ON candidacies USING gin (ballot_name gin_trgm_ops)');
            DB::statement('CREATE INDEX candidacies_civil_trgm_idx ON candidacies USING gin (civil_name gin_trgm_ops)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidacies');
    }
};
