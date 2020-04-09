<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Tests\Helpers;

/**
 * Description of User
 *
 * @author sobolevna
 */
class User extends \Illuminate\Foundation\Auth\User{
    
    protected $fillable = [
        'name', 
        'email',
        'password'
    ];
}
