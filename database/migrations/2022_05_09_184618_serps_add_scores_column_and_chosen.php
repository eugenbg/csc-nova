<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SerpsAddScoresColumnAndChosen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serps', function (Blueprint $table) {
            $table->json('scores')->nullable();
            $table->boolean('chosen')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serps', function (Blueprint $table) {
            $table->dropColumn('scores');
            $table->dropColumn('chosen');
        });
    }
}
