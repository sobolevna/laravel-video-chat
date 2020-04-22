<?php

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;
use Sobolevna\LaravelVideoChat\Models\Message;

class File extends Model {

    protected $table;
    protected $fillable = [
        'message_id', 'user_id', 'name',
    ];
    protected $appends = [
        'file_details',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.table.files_table');
    }

    /**
     * @return mixed
     */
    public function conversation() {
        return $this->morphTo();
    }

    /**
     * @return mixed
     */
    public function message() {
        return $this->belongsTo(Message::class, 'message_id');
    }

    /**
     * @return mixed
     */
    public function sender() {
        return $this->belongsTo(config('laravel-video-chat.user.model'), 'user_id');
    }

    public function getFileDetailsAttribute() {
        return get_file_details($this->name);
    }

}
