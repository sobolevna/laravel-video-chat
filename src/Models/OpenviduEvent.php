<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpenviduEvent extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'event_name',
        'event_data',
        'session_id'
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.openvidu_events_table');
    }
    
    public function conversation() {
        return $this->belongsTo(Conversation::class, 'session_id');
    }
}
