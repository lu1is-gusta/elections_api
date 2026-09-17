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
        Schema::create('electoral_units', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('tse_ue_code')->unique();
            $table->char('uf', 2)->nullable();
            $table->text('name');
            $table->text('kind');
            $table->text('ibge_code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('electoral_units');

            $table->index(['uf', 'kind'], 'electoral_units_uf_kind_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electoral_units');
    }
};
