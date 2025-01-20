<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHabilidadesTable extends Migration
{
    public function up()
    {
        Schema::create('habilidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('habilidades');
    }
}