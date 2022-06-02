<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeneratedHeadingsToGeneratedPieces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('generated_pieces', function (Blueprint $table) {
            $table->string('chosen_heading')->nullable()->after('heading');
            $table->json('generated_headings')->nullable()->after('chosen_heading');
            $table->boolean('chosen')->after('keyword_id')->change();
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
            $table->dropColumn('generated_headings');
            $table->dropColumn('chosen_heading');
        });
    }
}
