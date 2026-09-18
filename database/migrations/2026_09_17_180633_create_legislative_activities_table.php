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
        Schema::create('legislative_activities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('person_id')->constrained('people');
            $table->foreignId('mandate_id')->nullable()->constrained('political_mandates');
            $table->text('kind');
            $table->timestampTz('occurred_at');
            $table->text('title');
            $table->text('summary')->nullable();
            $table->text('url')->nullable();

            if (DB::getDriverName() === 'pgsql') {
                $table->jsonb('metadata')->default(DB::raw("'{}'::jsonb"));
            } else {
                $table->json('metadata')->default('{}');
            }

            $table->text('source');
        });

        DB::statement('CREATE INDEX activities_person_kind_idx ON legislative_activities (person_id, kind, occurred_at DESC, id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legislative_activities');
    }
};
