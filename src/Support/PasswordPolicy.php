<?php

namespace App\Support;

class PasswordPolicy
{
    public static function errors(string $password): array
    {
        $errors = [];

        if (preg_match_all('/[A-Z]/', $password) < 2) {
            $errors[] = 'at least 2 uppercase letters';
        }

        if (preg_match_all('/[a-z]/', $password) < 2) {
            $errors[] = 'at least 2 lowercase letters';
        }

        if (preg_match('/[0-9]/', $password) < 1) {
            $errors[] = 'at least 1 number';
        }

        if (preg_match('/[^A-Za-z0-9]/', $password) < 1) {
            $errors[] = 'at least 1 special character';
        }

        return $errors;
    }
}