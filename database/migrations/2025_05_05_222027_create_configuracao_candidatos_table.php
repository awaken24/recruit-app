<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracaoCandidatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracao_candidatos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidato_id')->unique();
        
            $table->boolean('notificacoes_email')->default(true);
            $table->boolean('notificacoes_whatsapp')->default(true);
            $table->boolean('receber_alertas_vagas')->default(true);
        
            $table->timestamps();
        
            $table->foreign('candidato_id')->references('id')->on('candidatos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracao_candidatos');
    }
}
