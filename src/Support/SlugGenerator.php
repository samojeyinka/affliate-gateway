<?php

namespace App\Support;

class SlugGenerator
{
    public static function make(string $base, callable $isTaken): string
    {
        $candidate = $base;
        $i = 1;

        while ($isTaken($candidate)) {
            $candidate = $base . '-' . $i;
            $i++;
        }

        return $candidate;
    }
}