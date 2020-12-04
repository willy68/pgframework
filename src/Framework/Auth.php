<?php

namespace Framework;

use Framework\Auth\User;

interface Auth
{

    /**
     *
     * @return User|null
     */
    public function getUser(): ?User;

    /**
     *
     * @param User $user
     * @return Auth
     */
    public function setUser(User $user): self;
}
