<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVagasTable extends Migration
{
    public function up() {
        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->unsignedBigInteger('empresa_id');
            $table->string('status')->default('ativa') ;
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->enum('perfil', ['frontend', 'backend', 'fullstack']);
            $table->enum('nivel_experiencia', ['junior', 'pleno', 'senior']);
            $table->text('descricao');
            $table->text('requisitos');
            $table->enum('modelo_trabalho', ['presencial_hibrido', 'remoto']);
            $table->string('endereco_trabalho')->nullable();
            $table->string('cidade_trabalho')->nullable();
            $table->text('comentarios_hibrido')->nullable();
            $table->enum('tipo_contrato', ['clt', 'pj', 'estagio']);
            $table->string('faixa_salarial');
            $table->boolean('divulgar_salario')->default(false);
            $table->boolean('vale_refeicao')->default(false);
            $table->boolean('vale_alimentacao')->default(false);
            $table->boolean('vale_transporte')->default(false);
            $table->boolean('plano_saude')->default(false);
            $table->boolean('plano_odontologico')->default(false);
            $table->boolean('seguro_vida')->default(false);
            $table->boolean('vale_estacionamento')->default(false);
            $table->boolean('academia_gympass')->default(false);
            $table->boolean('bonus')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vagas');
    }
}
