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
        Schema::create('political_mandates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('person_id')->constrained('people');
            $table->unsignedSmallInteger('office_id')->nullable();
            $table->text('house');
            $table->char('uf', 2)->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->smallInteger('legislature')->nullable();
            $table->unsignedInteger('party_id')->nullable();
            $table->text('external_id')->nullable();
            $table->text('source');

            $table->foreign('office_id')->references('id')->on('offices');
            $table->foreign('party_id')->references('id')->on('parties');
            $table->unique(['source', 'external_id']);
        });

        DB::statement('CREATE INDEX mandates_person_idx ON political_mandates (person_id, started_on DESC)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('political_mandates');
    }
};
