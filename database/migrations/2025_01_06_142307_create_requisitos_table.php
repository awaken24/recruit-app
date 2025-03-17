<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisitosTable extends Migration
{
    public function up(): void
    {
        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vaga_id')->constrained('vagas')->onDelete('cascade');
            $table->foreignId('habilidade_id')->constrained('habilidades');
            $table->string('tempo_experiencia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos');
    }
}
