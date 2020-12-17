<?php

namespace Framework\Auth\Service;

use RuntimeException;

class UtilToken implements UtilTokenInterface
{
    const SEPARATOR = ':';

    /**
     * Algorithme de cryptage
     *
     * @var string
     */
    protected $algo = 'sha256';

    /**
     * Initialise l'algorithme de cryptage si null vaut sha256 par défaut
     *
     * @param string|null $algo
     */
    public function __construct(?string $algo = null)
    {
        $this->setAlgo($algo);
    }
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
        $password = hash_hmac($this->algo, $credential . $password, $salt);
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
        $passwordToVerify = hash_hmac($this->algo, $credential . $password, $salt);
        list($credentialOrigin, $passwordOrigin) = $this->decodeToken($token);
        if (
            hash_equals($passwordToVerify, $passwordOrigin) &&
            $credentialOrigin === $credential
        ) {
            return true;
        }
        return false;
    }

    /**
     * Initialise l'algorithme de cryptage si null vaut sha256 par défaut
     *
     * @param string|null $algo
     * @return UtilTokenInterface
     */
    public function setAlgo(?string $algo = null): UtilTokenInterface
    {
        if (!is_null($algo)) {
            $availableAlgorithms = hash_algos();
            if (!in_array($algo, $availableAlgorithms, true)) {
                throw new RuntimeException(sprintf(
                    'The hash type `%s` was not found. Available algorithms are: %s',
                    $algo,
                    implode(', ', $availableAlgorithms)
                ));
            }
            $this->algo = strtolower($algo);
        }
        return $this;
    }
}
