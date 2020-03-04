<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-video-chat.table.conversations_users_table'), function (Blueprint $table) {
            $table->increments('id');            
            $table->unsignedInteger('conversation_id');
            $table->unsignedInteger('user_id');
            
            $table->timestamps();
            
            $table->unique(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-video-chat.table.conversations_users_table'));
    }
}
