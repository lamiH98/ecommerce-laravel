<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    protected $guard = 'admin';

    protected $fillable = [
        'title', 'body', 'user_id', 'image', 'title_ar', 'body_ar'
    ];

}
