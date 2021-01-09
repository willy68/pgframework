<?php

namespace Framework\Auth\Service;

class AuthSecurityToken
{
    public const SEPARATOR = ':';

    public static function generateSecurityToken(
        string $username,
        string $password,
        string $security
    ): string {
        $password = hash_hmac('sha256', $username . $password, $security);
        $username = base64_encode($username);
        return $username . self::SEPARATOR . $password;
    }

    public static function decodeSecurityToken(string $token): array
    {
        list($username, $password) = explode(self::SEPARATOR, $token);
        $username = base64_decode($username);
        return [$username, $password];
    }

    public static function validateSecurityToken(
        string $token,
        string $username,
        string $password,
        string $security
    ): bool {
        $passwordToVerify = hash_hmac('sha256', $username . $password, $security);
        list($usernameOrigin, $passwordOrigin) = self::decodeSecurityToken($token);
        if (
            hash_equals($passwordToVerify, $passwordOrigin) &&
            $usernameOrigin === $username
        ) {
            return true;
        }
        return false;
    }
}
