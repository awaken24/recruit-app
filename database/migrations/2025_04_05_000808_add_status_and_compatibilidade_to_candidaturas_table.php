<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndCompatibilidadeToCandidaturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->string('status')->default('pendente')->after('empresa_id');
            $table->unsignedTinyInteger('compatibilidade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropColumn(['status', 'compatibilidade']);
        });
    }
}
