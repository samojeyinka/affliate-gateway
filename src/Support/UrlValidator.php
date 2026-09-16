<?php

namespace App\Support;

class UrlValidator
{
    public static function isValid(mixed $url): bool
    {
        return is_string($url) && $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}