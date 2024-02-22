<?php

namespace App\Utils;

use App\Models\Option;

class Helpers {
    public static function get_option($key, $default = null) {
        $option = Option::where('name', $key)->first();
        if($option) {
            // Cast to correct type
            if($option->type == "boolean") {
                return $option->value == "true";
            } else if($option->type == "number") {
                return floatval($option->value);
            } else {
                return $option->value;
            }
        }
        return $default;
    }

}
