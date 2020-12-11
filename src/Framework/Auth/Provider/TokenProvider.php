<?php

namespace Framework\Auth\Provider;

use Framework\Auth\TokenInterface;

interface TokenProvider
{

    /**
     * get cookie token from database or what else
     *
     * @param string $credential
     * @return \Framework\Auth\TokenInterface|null
     */
    public function getToken(string $credential): ?TokenInterface;
}
