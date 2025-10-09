<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User_role extends Model
{
    protected $table = 'user_roles';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'role_id',
    ];
}


