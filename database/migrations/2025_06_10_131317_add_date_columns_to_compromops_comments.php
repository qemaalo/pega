<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateColumnsToCompromopsComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('compromops_comments', function (Blueprint $table) {
            $table->date('finicio')->nullable()->after('usuario');
            $table->date('ftermino')->nullable()->after('finicio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compromops_comments', function (Blueprint $table) {
            $table->dropColumn(['finicio', 'ftermino']);
        });
    }
}
