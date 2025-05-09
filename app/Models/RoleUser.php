<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    use HasFactory;

    protected $table = 'role_users';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'user_id',
    ];
}
