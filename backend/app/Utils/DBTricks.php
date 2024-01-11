<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

class DBTricks {
    public static function nameSelect($dest = "name", $source_table=null) {
        if(!is_null($source_table)) {
            $source_table .= ".";
        } else {
            $source_table = "";
        }
        return DB::raw("CONCAT_WS(' ', NULLIF({$source_table}surname, ''), {$source_table}name) AS $dest");
    }
}
