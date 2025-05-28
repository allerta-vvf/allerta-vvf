<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class HttpClient {
    public static function defaultClient() {
        $owner = Config::get("features.owner");
        $userAgent = "AllertaVVF";
        if ($owner) {
            $userAgent .= " ($owner)";
        }
        return Http::withHeaders(['User-Agent' => $userAgent]);
    }
}
