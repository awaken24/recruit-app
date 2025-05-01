<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailTemplatesToConfiguracaoEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configuracao_empresas', function (Blueprint $table) {
            $table->text('email_template_sucesso')->nullable()->after('whatsapp_template');
            $table->text('email_template_recusado')->nullable()->after('email_template_sucesso');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configuracao_empresas', function (Blueprint $table) {
            $table->dropColumn('email_template_sucesso');
            $table->dropColumn('email_template_recusado');
        });
    }
}
