<?php

namespace Framework\Environnement;

class Environnement
{
    /**
     * return environnement variable if is set else default value or null
     * 
     * @todo Add probably base64_decode if needed
     *
     * @param string $var
     * @param string|null $default
     * @return string|null
     */
    static public function getEnv(string $var, ?string $default = null): ?string
    {
        if (!isset($_ENV[$var]) || !isset($_SERVER[$var])) {
            return $default;
        }

        $env = $_ENV[$var];
        if ('base64:' === mb_substr($env, 0, 7)) {
            $env = mb_substr($env, 7);
        }
        return $env;
    }
}
