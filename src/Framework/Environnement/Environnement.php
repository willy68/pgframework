<?php

namespace Framework\Environnement;

class Environnement
{
    static public function getEnv(string $var, string $default)
    {
        if (!isset($_ENV[$var])) {
            return $default;
        }
        return $_ENV[$var];
    }
}
