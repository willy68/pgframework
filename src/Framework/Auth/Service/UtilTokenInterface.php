<?php

namespace Framework\Auth\Service;

/**
 * Functions utiles pour la génération,
 * le décodage et la validation d'un token
 */
interface UtilTokenInterface
{
    /**
     * Génère un token à partir des champs credential, password et salt
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
    ): string;

    /**
     * Retourne les différentes parties du token en un tableau,
     * s'il n'est fait que d'une partie le tableau n'aura qu'une entrée
     *
     * @param string $token Le token a décoder
     * @return array
     */
    public function decodeToken(string $token): array;

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
    ): bool;
}
