<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia');
            $table->string('razao_social');
            $table->string('cnpj', 18)->unique();
            $table->string('telefone')->nullable();
            $table->string('email')->unique();
            $table->text('descricao')->nullable();
            $table->boolean('sem_cnpj')->default(false);
            $table->string('website')->nullable();
            $table->string('youtube_video')->nullable();
            $table->enum('tipo_empresa', ['startup', 'pequena_media', 'grande'])->nullable();
            $table->integer('ano_fundacao')->nullable();
            $table->integer('numero_funcionarios')->nullable();
            $table->text('politica_remoto')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('youtube')->nullable();
            $table->string('contato_nome');
            $table->string('contato_cargo');
            $table->string('contato_telefone');
            $table->string('como_encontrou');
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
        Schema::dropIfExists('empresas');
    }
}
