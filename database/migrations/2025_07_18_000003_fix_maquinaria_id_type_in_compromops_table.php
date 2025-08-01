<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixMaquinariaIdTypeInCompromopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First try to drop the existing foreign key constraint if it exists
        try {
            Schema::table('compromops', function (Blueprint $table) {
                $table->dropForeign(['maquinaria_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, that's fine
        }

        // Change the column type to unsigned integer to match maquinarias.id
        Schema::table('compromops', function (Blueprint $table) {
            $table->unsignedInteger('maquinaria_id')->nullable()->change();
        });

        // Add the foreign key constraint
        Schema::table('compromops', function (Blueprint $table) {
            $table->foreign('maquinaria_id')->references('id')->on('maquinarias')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the foreign key constraint
        Schema::table('compromops', function (Blueprint $table) {
            $table->dropForeign(['maquinaria_id']);
        });

        // Change back to signed integer
        Schema::table('compromops', function (Blueprint $table) {
            $table->integer('maquinaria_id')->nullable()->change();
        });
    }
}
