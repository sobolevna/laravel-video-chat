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

        Schema::create(config('laravel-video-chat.table.openvidu_events_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_id');
            $table->string('event_name');
            $table->json('event_data');

            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create(config('laravel-video-chat.table.participants_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('session_id');
            $table->string('participant_id');
            $table->string('location')->nullable();
            $table->string('platform');
            $table->string('client_data')->nullable();
            $table->string('server_data')->nullable();
            $table->datetime('start_time')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('reason', ["disconnect","forceDisconnectByUser","forceDisconnectByServer","sessionClosedByServer","networkDisconnect","openviduServerStopped"])->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('laravel-video-chat.table.connections_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('session_id');
            $table->string('participant_id');
            $table->enum('connection', ["INBOUND","OUTBOUND"]);
            $table->string('receiving_from')->nullable();
            $table->boolean('audio_enabled')->default(true);
            $table->boolean('video_enabled')->default(true);
            $table->enum('video_source',["CAMERA","SCREEN"]);
            $table->string('video_framerate');
            $table->string('video_dimensions');
            $table->datetime('start_time')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('reason', [
                "unsubscribe",
                "unpublish",
                "disconnect",
                "forceUnpublishByUser",
                "forceUnpublishByServer",
                "forceDisconnectByUser",
                "forceDisconnectByServer",
                "sessionClosedByServer",
                "networkDisconnect",
                "openviduServerStopped",
                "mediaServerDisconnect"
            ])->nullable();

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
            $table->integer("size")->nullable();
            $table->datetime('start_time');
            $table->float("duration")->nullable();
            $table->string('url')->nullable();
            $table->boolean('has_audio')->default(true);
            $table->boolean('has_video')->default(true);
            $table->enum('status', ["started","stopped","ready","failed"]);
            $table->enum('reason', [
                "recordingStoppedByServer",
                "lastParticipantLeft",
                "sessionClosedByServer",
                "automaticStop",
                "openviduServerStopped",
                "mediaServerDisconnect"
            ])->nullable();

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

        Schema::dropIfExists(config('laravel-video-chat.table.openvidu_events_table'));
        Schema::dropIfExists(config('laravel-video-chat.table.participants_table'));
        Schema::dropIfExists(config('laravel-video-chat.table.connections_table'));
        Schema::dropIfExists(config('laravel-video-chat.table.recordings_table'));
    }
}
