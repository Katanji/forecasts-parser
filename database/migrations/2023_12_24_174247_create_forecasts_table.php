<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->text('teams');
            $table->string('sport_type', 128)->default('');
            $table->string('prediction', 64)->default('');
            $table->integer('attractiveness');
            $table->string('date', 24);
            $table->string('last_results', 36);
            $table->integer('profit');
            $table->float('coefficient');
            $table->text('explanation');
            $table->string('author', 24);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
