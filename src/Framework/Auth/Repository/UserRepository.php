<?php

namespace Framework\Auth\Repository;

use Framework\Auth\User;

interface UserRepository
{
    public function getUser(string $field, $value): ?User;
}
