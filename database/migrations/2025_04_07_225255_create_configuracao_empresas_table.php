<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracaoEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracao_empresas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->unique();

            $table->boolean('whatsapp_ativo')->default(false);
            $table->string('whatsapp_token')->nullable();
            $table->string('whatsapp_instance')->nullable();
            $table->text('whatsapp_template')->nullable();
            $table->text('whatsapp_security_token')->nullable();

            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracao_empresas');
    }
}
