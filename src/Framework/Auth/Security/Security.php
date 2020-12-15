<?php

namespace Framework\Auth\Security;


class Security
{

    public static function randomPassword(int $length): string
    {
        return substr(
            bin2hex(Security::randomBytes((int)ceil($length / 2))),
            0,
            $length
        );
    }

    public static function randomBytes(int $length): string
    {
        return random_bytes($length);
    }
}