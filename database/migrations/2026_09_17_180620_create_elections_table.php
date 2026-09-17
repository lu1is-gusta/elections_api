<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->smallInteger('year');
            $table->integer('tse_election_id');
            $table->text('name');
            $table->text('kind');
            $table->text('scope');
            $table->date('election_date')->nullable();

            $table->unique(['year', 'tse_election_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
