<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogSuccess extends Model{
    use HasFactory;

    protected $table = 'recruit_app.log_success';

    protected $fillable = [
        'route',
        'success_message',
        'user_id',
    ];
}
