<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\User;
use Cake\Utility\Security;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Auth\Service\UtilTokenInterface;
use Framework\Auth\Repository\UserRepositoryInterface;
use Framework\Auth\Repository\TokenRepositoryInterface;
use RuntimeException;

class RememberMeDatabase extends RememberMe
{

    /**
     * Token Repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * Constructeur: ajoute l'option pour le nom du cookie du mot de passe aléatoire
     *
     * @param \Framework\Auth\Repository\UserRepositoryInterface $userRepository
     * @param \Framework\Auth\Service\UtilTokenInterface $utilToken
     * @param \Framework\Auth\Repository\TokenRepositoryInterface $tokenRepository
     * @param array $options
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UtilTokenInterface $utilToken,
        TokenRepositoryInterface $tokenRepository,
        array $options = ['password_cookie_name' => 'random_password']
    ) {
        parent::__construct($userRepository, $utilToken, $options);
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Crée un cookie d'authentification,
     * un token en base données et un cookie avec un mot de passe aléatoire
     *
     * @param ResponseInterface $response
     * @param string $credential
     * @param string $password
     * @param string $secret
     * @return ResponseInterface
     */
    public function onLogin(
        ResponseInterface $response,
        string $credential,
        string $password
    ): ResponseInterface {
        $response = parent::onLogin($response, $credential, $password);
        $expirationDate = date('Y-m-d H:i:s', $this->expirationDate);
        $randomPassword = Security::randomString(24);
        //['credential', 'random_password', 'expiration_date', 'is_expired']
        $this->tokenRepository->saveToken(
            [
                'credential' => $credential,
                'random_password' => password_hash(
                    $randomPassword,
                    PASSWORD_BCRYPT,
                    ["cost" => 10]
                ),
                'expiration_date' => $expirationDate,
                'is_expired' => false
            ]
        );
        $cookie = SetCookie::create($this->options['password_cookie_name'])
            ->withValue($randomPassword)
            ->withExpires($this->expirationDate)
            ->withPath($this->options['path'])
            ->withDomain(null)
            ->withSecure(false)
            ->withHttpOnly(false);
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     * Connecte l'utilisateur automatiquement avec le cookie reçu de la requète et 
     * vérifie le token en base de données s'il est valide
     *
     * @param ServerRequestInterface $request
     * @param string $secret
     * @return User|null
     */
    public function autoLogin(ServerRequestInterface $request): ?User
    {
        $user = parent::autoLogin($request);
        if ($user) {
            $getUserField = 'get' . ucfirst($this->options['field']);
            if (!method_exists($user, $getUserField)) {
                throw new RuntimeException(
                    "$user class method $getUserField does not exist"
                );
            }
            $dbToken = $this->tokenRepository->getToken($user->$getUserField());
            $cookie = FigRequestCookies::get(
                $request,
                $this->options['password_cookie_name']
            );
            if ($dbToken && $cookie->getValue()) {
                $authenticate = true;
                //password corrupted
                if (!password_verify($cookie->getValue(), $dbToken->getRandomPassword())) {
                    $authenticate = false;
                }
                // expiration date outdated
                $this->expirationDate = time() + $this->options['expires'];
                if ($dbToken->getExpirationDate() < date('Y-m-d H:i:s', $this->expirationDate)) {
                    $authenticate = false;
                }
                // database token marked as expired
                if ($dbToken->getIsExpired()) {
                    $authenticate = false;
                }
                if (!$authenticate) {
                    $this->tokenRepository->updateToken(['is_expired' => 1], $dbToken->getId());
                    return null;
                }
            }
        }
        return $user;
    }

    /**
     * Déconnecte l'utilisateur et invalide le cookie dans la response et
     * marque le token en base de données expiré
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request,  ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            list($credential, $password) = $this->utilToken->decodeToken($cookie->getValue());
        }
        $response = parent::onLogout($request, $response);
        $dbToken = $this->tokenRepository->getToken($credential);
        // Set database token mark as expired
        $this->tokenRepository->updateToken(['is_expired' => 1], $dbToken->getId());

        return $response;
    }

    /**
     * Renouvelle la date d'expiration du cookie dans la response et le token en base de données
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Set $this->expirationDate
        $response = parent::resume($request, $response);

        // Get $credential
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            list($credential, $password) = $this->utilToken->decodeToken($cookie->getValue());
        }
        $dbToken = $this->tokenRepository->getToken($credential);
        // Set database token a new expiration_date
        $this->tokenRepository->updateToken(
            ['expiration_date' => date('Y-m-d H:i:s', $this->expirationDate)],
            $dbToken->getId()
        );

        return $response;
    }
}
