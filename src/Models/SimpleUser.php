<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of SimpleUser
 *
 * @author sobolevna
 */
class SimpleUser extends Model {
    
    protected $table;    
    
    public $fillable = ['name'];
    
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->table = config('laravel-video-chat.user.table');
    }
}
