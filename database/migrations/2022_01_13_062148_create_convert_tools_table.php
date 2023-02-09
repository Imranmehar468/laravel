<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConvertToolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('convert_tools', function (Blueprint $table) {
            $table->id();
            $table->string('uuid_name')->unique();
            $table->string('file_name');
            $table->string('upload_path');
            $table->enum('action',['excel_to_pdf','word_to_pdf','ppt_to_pdf']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('convert_tools');
    }
}
