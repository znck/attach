<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('filename')->nullable();
            $table->string('collection')->nullable();
            $table->string('disk');
            $table->string('path');
            $table->string('mime');
            $table->string('visibility');
            $table->unsignedInteger('size');
            $table->unsignedInteger('order')->default(0)->nullable();
            $table->json('manipulations')->default('[]');
            $table->json('properties')->default('[]');
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
        Schema::drop('attachments');
    }
}
