<?php

namespace Framework\Auth\Repository;

use Framework\Auth\User;

interface UserRepositoryInterface
{
    public function getUser(string $field, $value): ?User;
}
