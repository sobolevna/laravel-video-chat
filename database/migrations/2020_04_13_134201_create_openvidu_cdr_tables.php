<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid;

class CreateOpenviduCdrTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table(config('laravel-video-chat.table.conversations_table'), function (Blueprint $table) {
        //     $table->uuid('session_id', Uuid::uuid4()->toString());
        // });

        Schema::create(config('laravel-video-chat.table.openvidu_logs_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('eventData');

            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create(config('laravel-video-chat.table.participants_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('session_id');
            $table->string('participant_id');
            $table->string('location')->nullable();
            $table->string('platform');
            $table->string('client_data');
            $table->string('server_data');
            $table->datetime('start_time');
            $table->integer('duration');
            $table->string('reason');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('laravel-video-chat.table.connections_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('session_id');
            $table->string('connection');
            $table->string('receiving_from');
            $table->boolean('audio_enabled');
            $table->boolean('video_enabled');
            $table->string('video_source');
            $table->string('video_framerate');
            $table->string('video_dimensions');
            $table->datetime('start_time');
            $table->integer('duration');
            $table->string('reason');

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create(config('laravel-video-chat.table.recordings_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('recording_id');
            $table->string('name');
            $table->string('output_mode');
            $table->string('resolution');
            $table->string('recording_layout');
            $table->string('session_id');
            $table->integer("size");
            $table->datetime('start_time');
            $table->float("duration");
            $table->string('url');
            $table->boolean('has_audio');
            $table->boolean('has_video');
            $table->string('status');
            $table->string('reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table(config('laravel-video-chat.table.conversations_table'), function (Blueprint $table) {
        //     $table->dropColumn('session_id', Uuid::uuid4()->toString());
        // });

        Schema::dropIfExists(config('laravel-video-chat.table.openvidu_logs_table'));
        Schema::dropIfExists(config('laravel-video-chat.table.participants_table'));
        Schema::dropIfExists(config('laravel-video-chat.table.connections_table'));
        Schema::dropIfExists(config('laravel-video-chat.table.recordings_table'));
    }
}
