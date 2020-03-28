<?php

namespace Framework\Auth;

interface User
{

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     * Undocumented function
     *
     * @return string[]
     */
    public function getRoles(): array;
}
