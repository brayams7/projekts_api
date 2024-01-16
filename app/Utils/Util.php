<?php

namespace App\Utils;

class Util{

    public static function generateVerificationCode($size = 4): string {
        $characters = '0123456789';

        $code = '';

        for ($i = 0; $i < $size; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }
}