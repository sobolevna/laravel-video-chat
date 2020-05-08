<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;

class OpenviduParticipant extends Model
{

    protected $table;

    protected $fillable = [
        'eventData'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.openvidu_logs_table');
    }
    
    public function conversation() {
        return $this->belongsTo(Conversation::class, 'session_id');
    }
}
