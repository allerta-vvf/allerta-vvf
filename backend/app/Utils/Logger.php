<?php

namespace App\Utils;

use App\Models\User;
use App\Models\Log;

class Logger {
    public static function log(string $action, User $changed = null, User $editor = null, $source_type = "web")
    {
        $log = new Log();
        $log->action = $action;

        if(is_null($changed)) $changed = auth()->user();
        $log->changed()->associate($changed);
        if(is_null($editor)) $editor = auth()->user();
        $log->editor()->associate($editor);

        $request = request();
        if($source_type !== "web") {
            $log->ip = null;
            $log->source_type = $source_type;
            $log->user_agent = null;
        } else {
            $log->source_type = "web";
            $request = request();
            if(!is_null($request)) {
                $log->ip = $request->ip();
                $log->user_agent = $request->userAgent();
            } else {
                $log->ip = null;
                $log->user_agent = null;
            }
        }

        $log->save();

        User::where('id', $editor->id)->update(['last_access' => now()]);
    }
}
