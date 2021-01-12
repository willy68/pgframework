<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RememberMeInterface
{

    /**
     * Crée un cookie d'authentification
     *
     * @param ResponseInterface $response
     * @param string $username
     * @param string $password
     * @param string $secret
     * @return ResponseInterface
     */
    public function onLogin(
        ResponseInterface $response,
        string $username,
        string $password,
        string $salt = ''
    ): ResponseInterface;

    /**
     * Connecte l'utilisateur automatiquement avec le cookie reçu de la requète
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string $salt
     * @return \Framework\Auth\User|null
     */
    public function autoLogin(ServerRequestInterface $request, string $salt = ''): ?User;

    /**
     * Déconnecte l'utilisateur et invalide le cookie dans la response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    /**
     * Renouvelle la date d'expiration du cookie dans la response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
