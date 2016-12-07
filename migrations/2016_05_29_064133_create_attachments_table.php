<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('attach.table', 'attachments'), function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title')->nullable();
            $table->string('disk')->nullable();
            $table->string('path');
            $table->string('mime');
            $table->string('filename');
            $table->string('extension');
            $table->unsignedInteger('size');
            $table->string('visibility');
            $table->json('variations')->default('[]');
            $table->json('additional')->nullable();
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
        Schema::drop(config('attach.table', 'attachments'));
    }
}
