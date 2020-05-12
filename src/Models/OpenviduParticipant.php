<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpenviduParticipant extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'user_id',
        'session_id',
        'participant_id',
        'location',
        'platform',
        'client_data',
        'server_data',
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
        $this->table = config('laravel-video-chat.table.participants_table');
    }
    
    public function conversation() {
        return $this->belongsTo(Conversation::class, 'session_id');
    }
}
