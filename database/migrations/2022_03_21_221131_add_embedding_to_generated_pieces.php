<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmbeddingToGeneratedPieces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('generated_pieces', function (Blueprint $table) {
            $table->json('embedding')->nullable();
            $table->boolean('chosen')->nullable();
            $table->float('distance_from_original');
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
            $table->dropColumn('embedding');
            $table->dropColumn('chosen');
            $table->dropColumn('distance_from_original');
        });
    }
}
