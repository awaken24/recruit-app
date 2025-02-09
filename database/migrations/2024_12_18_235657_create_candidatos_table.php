<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCandidatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('sobrenome');
            $table->string('cpf', 14)->unique();
            $table->text('descricao')->nullable();
            $table->string('experienceLevel')->nullable();
            $table->string('foco_carreira')->nullable();
            $table->string('gitHub')->nullable();
            $table->string('linkedIn')->nullable();
            $table->string('nivelIngles')->nullable();
            $table->boolean('pcd')->default(false);
            $table->string('salario_desejado')->nullable();
            $table->string('status_busca')->nullable();
            $table->string('telefone')->nullable();
            $table->string('tipo_contrato')->nullable();
            $table->string('tipo_empresa')->nullable();
            $table->string('titulo')->nullable();
            $table->boolean('trabalho_remoto')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidatos');
    }
}
