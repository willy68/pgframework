<?php

namespace Framework\Environnement;

class Environnement
{
    /**
     * return environnement variable if is set else default value or null
     *
     * @param string $var
     * @param string|null $default
     * @return string|null
     */
    static public function getEnv(string $var, ?string $default = null)
    {
        if (!isset($_ENV[$var]) || !isset($_SERVER[$var])) {
            return $default;
        }
        return $_ENV[$var];
    }
}
