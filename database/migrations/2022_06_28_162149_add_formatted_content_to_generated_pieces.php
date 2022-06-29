<?php

use App\Models\GeneratedPiece;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFormattedContentToGeneratedPieces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('generated_pieces', function (Blueprint $table) {
            $table->longText('formatted_content')->after('content')->nullable();
        });

        GeneratedPiece::query()->update(['formatted_content' => DB::raw('content')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('generated_pieces', function (Blueprint $table) {
            $table->dropColumn('formatted_content');
        });
    }
}
