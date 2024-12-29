<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnderecosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('enderecos')) {
            Schema::create('enderecos', function (Blueprint $table) {
                $table->id();
                $table->morphs('enderecavel');
                $table->string('logradouro');
                $table->string('numero')->nullable();
                $table->string('complemento')->nullable();
                $table->string('bairro');
                $table->string('cidade');
                $table->string('estado');
                $table->string('cep');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enderecos');
    }
}
