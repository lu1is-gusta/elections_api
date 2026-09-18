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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->text('civil_name');
            $table->text('ballot_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->char('birth_uf', 2)->nullable();
            $table->text('birth_city')->nullable();
            $table->smallInteger('gender_code')->nullable();
            $table->smallInteger('race_code')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
