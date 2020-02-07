<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    protected $table;

    protected $fillable = [
        'user_id', 'text',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.messages_table');
    }
    
    /**
     * @return mixed
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * @return mixed
     */
    public function sender()
    {
        return $this->belongsTo(config('laravel-video-chat.user.model'), 'user_id');
    }

    /**
     * @return mixed
     */
    public function files()
    {
        return $this->hasMany(File::class, 'message_id');
    }
}
