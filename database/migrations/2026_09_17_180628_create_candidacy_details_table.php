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
        Schema::create('candidacy_details', function (Blueprint $table) {
            $table->foreignId('candidacy_id')->primary()->constrained('candidacies')->cascadeOnDelete();
            $table->text('coalition_name')->nullable();
            $table->text('coalition_composition')->nullable();
            $table->text('federation_acronym')->nullable();
            $table->text('nationality')->nullable();
            $table->text('marital_status')->nullable();
            $table->text('process_number')->nullable();
            $table->boolean('replaced')->nullable();
            $table->unsignedBigInteger('replaced_sq')->nullable();

            if (DB::getDriverName() === 'pgsql') {
                $table->jsonb('extra')->default(DB::raw("'{}'::jsonb"));
            } else {
                $table->json('extra')->default('{}');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidacy_details');
    }
};
