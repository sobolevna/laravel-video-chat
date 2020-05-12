<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Connection extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'user_id', 
        'session_id',
        'participant_id',
        'connection',
        'receiving_from',
        'audio_enabled',
        'video_enabled',
        'video_source',
        'video_framerate',
        'video_dimensions',
        'start_time',
        'duration',
        'reason'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.connections_table');
    }

    public function user() {
        return $this->belongsTo(config('laravel-video-chat.user.model'));
    }

    public function conversation() {
        return $this->belongsTo(Conversation::class, 'session_id');
    }
}
