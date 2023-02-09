<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllowedFileSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allowed_file_sizes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('filetype')->unique();
            $table->integer('maxuploadsize')->default(5*1024*1024);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allowed_file_sizes');
    }
}
