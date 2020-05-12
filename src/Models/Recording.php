<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recording extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'recording_id',
        'name',
        'output_mode',
        'resolution',
        'recording_layout',
        'session_id',
        "size",
        'start_time',
        "duration",
        'url',
        'has_audio',
        'has_video',
        'status',
        'reason'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.recordings_table');
    }
    
    public function conversation() {
        return $this->belongsTo(Conversation::class, 'session_id');
    }
}
