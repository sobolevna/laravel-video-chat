<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{

    protected $table;

    protected $fillable = [
        'name', 'type',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.conversations_table');
    }
    
    /**
     * @return mixed
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return mixed
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function users() {
        return $this->belongsToMany(config('laravel-video-chat.user.model'), 'conversations_users', 'conversation_id', 'user_id');
    }
}
