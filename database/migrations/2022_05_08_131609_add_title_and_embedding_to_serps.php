<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleAndEmbeddingToSerps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serps', function (Blueprint $table) {
            $table->text('title');
            $table->longText('title_embedding')->nullable();
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
            $table->dropColumn('title');
            $table->dropColumn('title_embedding');
        });
    }
}
