<?php

namespace Framework\Auth\Repository;

use Framework\Auth\TokenInterface;

interface TokenRepositoryInterface
{

    /**
     * get cookie token from database or what else
     *
     * @param mixed $credential
     * @return \Framework\Auth\TokenInterface|null
     */
    public function getToken($credential): ?TokenInterface;

    /**
     * Sauvegarde le token (database, cookie, les deux ou autre)
     *
     * @param array $token
     * @return TokenInterface|null
     */
    public function saveToken(array $token): ?TokenInterface;

    /**
     * Mise à jour du token en database
     *
     * @param array $token
     * @param mixed $id
     * @return TokenInterface|null
     */
    public function updateToken(array $token, $id): ?TokenInterface;

    /**
     * Detruit le token inutile
     *
     * @param int $id
     * @return void
     */
    public function deleteToken(int $id);
}
