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
        Schema::create('candidacy_assets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('candidacy_id')->constrained('candidacies')->cascadeOnDelete();
            $table->integer('tse_order')->nullable();
            $table->text('type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('value', 15, 2)->nullable();

            $table->index('candidacy_id', 'candidacy_assets_cid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidacy_assets');
    }
};
