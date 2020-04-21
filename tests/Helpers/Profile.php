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
class Profile extends \Illuminate\Database\Eloquent\Model{

    protected $table = 'profile';
    
    protected $fillable = [
        'name',         
    ];

}
