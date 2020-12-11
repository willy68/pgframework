<?php

namespace Framework\Auth\Repository;

use Framework\Auth\TokenInterface;

interface TokenRepositoryInterface
{

    /**
     * get cookie token from database or what else
     *
     * @param string $credential
     * @return \Framework\Auth\TokenInterface|null
     */
    public function getToken(string $credential): ?TokenInterface;
}
