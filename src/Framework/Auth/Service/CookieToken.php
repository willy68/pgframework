<?php

namespace Framework\Auth\Service;

class CookieToken implements UtilTokenInterface
{
    const SEPARATOR = ':';

    /**
     * Génère un token à partir des champs credential, password et sécurity
     *
     * @param string $credential (ex. username ou email)
     * @param string $password mot de passe généré par la fonction password_hash
     * habituellement
     * @param string $salt par défaut à une chaine vide
     * mais peut être une variable d'environnement ou autre
     * @return string
     */
    public function getToken(
        string $credential,
        string $password,
        string $salt = ''
    ): string
    {
        $password = hash_hmac('sha256', $credential . $password, $salt);
        $credential = base64_encode($credential);
        return $credential . self::SEPARATOR . $password;
    }

    /**
     * Retourne les différentes parties du token en un tableau,
     * s'il n'est fait que d'une partie le tableau n'aura qu'une entrée
     *
     * @param string $token Le token a décoder
     * @return array
     */
    public function decodeToken(string $token): array
    {
        list($credential, $password) = explode(self::SEPARATOR, $token);
        $credential = base64_decode($credential);
        return [$credential, $password];
    }

    /**
     * Valide le token avec les données credential, password et security
     *
     * @param string $token
     * @param string $credential (ex. username ou email)
     * @param string $password mot de passe généré par la fonction password_hash
     * habituellement
     * @param string $salt par défaut à une chaine vide
     * mais peut être une variable d'environnement ou autre
     * @return bool
     */
    public function validateToken(
        string $token,
        string $credential,
        string $password,
        string $salt = ''
    ): bool
    {
        $passwordToVerify = hash_hmac('sha256', $credential . $password, $salt);
        list($usernameOrigin, $passwordOrigin) = $this->decodeToken($token);
        if (
            hash_equals($passwordToVerify, $passwordOrigin) &&
            $usernameOrigin === $credential
        ) {
            return true;
        }
        return false;
    }
}
