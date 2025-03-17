<?php

namespace App\Helpers;

use App\Models\Logs;
use Illuminate\Support\Facades\Auth;

class LogHelper{

    public static function saveLog($action, $description){
        Logs::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip()
        ]);
    }
}
