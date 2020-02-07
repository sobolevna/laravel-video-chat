<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SimpleUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('laravel-video-chat.settings.simple-users')) {
            Schema::create(config('laravel-video-chat.user.table'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config('laravel-video-chat.settings.simple-users')) {
            Schema::dropIfExists(config('laravel-video-chat.user.table'));
        }
    }
}
