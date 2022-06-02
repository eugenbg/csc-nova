<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParamSerpIdToGeneratedPieces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('generated_pieces', function (Blueprint $table) {
            $table->unsignedBigInteger('serp_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('generated_pieces', function (Blueprint $table) {
            $table->dropColumn('serp_id');
        });
    }
}
