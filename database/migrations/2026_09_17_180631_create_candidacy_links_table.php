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
        Schema::create('candidacy_links', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('candidacy_id')->constrained('candidacies')->cascadeOnDelete();
            $table->text('kind');
            $table->text('url');

            $table->index('candidacy_id', 'candidacy_links_cid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidacy_links');
    }
};
