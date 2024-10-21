<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

class HttpClient {
    public static function defaultClient() {
        $owner = $config("features.owner");
        $userAgent = "AllertaVVF";
        if ($owner) {
            $userAgent .= " ($owner)";
        }
        return Http::withHeaders(['User-Agent' => $userAgent]);
    }
}
