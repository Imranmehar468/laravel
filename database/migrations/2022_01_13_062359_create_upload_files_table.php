<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_files', function (Blueprint $table) {
            $table->id();
            $table->string('request_api_id');
            $table->string('uuid_name')->unique();
            $table->string('file_name');
            $table->integer('size');
            $table->string('upload_path');
            $table->string('split_range')->default('');
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
        Schema::dropIfExists('upload_files');
    }
}
