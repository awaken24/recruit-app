<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogErrors extends Model{
    use HasFactory;
    protected $table = 'recruit_app.log_errors';

    protected $fillable = [
        'route',
        'error_message',
        'user_id'
    ];
}
