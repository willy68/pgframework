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
        string $password,
        string $salt = ''
    ): ResponseInterface {

        $response = parent::onLogin($response, $credential, $password, $salt);
        $randomPassword = Security::randomString(64);

        //['credential', 'random_password', 'expiration_date', 'is_expired']
        $token = $this->tokenRepository->saveToken(
            [
                'credential' => $credential,
                'random_password' => password_hash(
                    $randomPassword,
                    PASSWORD_BCRYPT,
                    ["cost" => 10]
                ),
                'expiration_date' => (new \DateTime)->setTimestamp($this->expirationDate),
                'is_expired' => false
            ]
        );

        // Create random password cookie
        $cookie = SetCookie::create($this->options['password_cookie_name'])
            ->withValue($randomPassword)
            ->withExpires($this->expirationDate)
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
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
    public function autoLogin(ServerRequestInterface $request, string $salt = ''): ?User
    {
        $user = parent::autoLogin($request, $salt);
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

            if (!$dbToken || !$cookie->getValue()) {
                return null;
            }

            $authenticate = true;
            // database token marked as expired
            if ($dbToken->getIsExpired()) {
                $authenticate = false;
            }
            //password corrupted
            if (!password_verify($cookie->getValue(), $dbToken->getRandomPassword())) {
                $authenticate = false;
            }
            // expiration outdated
            if ($dbToken->getExpirationDate()->getTimestamp() < time()) {
                $authenticate = false;
            }
            if (!$authenticate) {
                $this->tokenRepository->updateToken(
                    [
                        'is_expired' => 1,
                        'expiration_date' => (new \DateTime)->setTimestamp(time() - 3600)
                    ],
                    $dbToken->getId()
                );
                return null;
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
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            list($credential, ) = $this->utilToken->decodeToken($cookie->getValue());

            $response = parent::onLogout($request, $response);
            $dbToken = $this->tokenRepository->getToken($credential);

            // Delete token from database
            $this->tokenRepository->deleteToken($dbToken->getId());

            // Delete cookie
            $cookiePassword = SetCookie::create($this->options['password_cookie_name'])
            ->withValue('')
            ->withExpires(time() - 3600)
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
            $response = FigResponseCookies::set($response, $cookiePassword);
        }
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
            list($credential, ) = $this->utilToken->decodeToken($cookie->getValue());
        
            $dbToken = $this->tokenRepository->getToken($credential);

            $randomPassword = Security::randomString(64);
            // Set database token a new expiration_date
            $this->tokenRepository->updateToken(
                [
                'random_password' => password_hash(
                    $randomPassword,
                    PASSWORD_BCRYPT,
                    ["cost" => 10]
                ),
                'expiration_date' => (new \DateTime)->setTimestamp($this->expirationDate)
            ],
                $dbToken->getId()
            );

            // Set new random password cookie
            $cookie = SetCookie::create($this->options['password_cookie_name'])
            ->withValue($randomPassword)
            ->withExpires($this->expirationDate)
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
            $response = FigResponseCookies::set($response, $cookie);
        }
        return $response;
    }
}
