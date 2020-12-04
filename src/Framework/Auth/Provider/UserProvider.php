<?php

namespace Framework\Auth\Provider;

use Framework\Auth\User;

interface UserProvider
{
    public function getUser(string $field, $value): ?User;
}
