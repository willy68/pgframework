<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\User;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Framework\Auth\Repository\TokenRepositoryInterface;
use Framework\Auth\Repository\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Auth\Service\UtilTokenInterface;

class RememberMeDatabase implements RememberMeInterface
{
    private $salt = 'pass_phrase';

    /**
     * Cookie options
     *
     * @var array
     */
    private $options = [
        'name' => 'auth_login',
        'field' => 'username',
        'expires' => 3600 * 24 * 3,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => false
    ];

    /**
     * User Repository
     * 
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * Token Repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * Utilitaire de codage et décodage du token
     *
     * @var UtilTokenInterface
     */
    private $cookieToken;

    public function __construct(
    UserRepositoryInterface $userRepository,
    UtilTokenInterface $cookieToken,
    TokenRepositoryInterface $tokenRepository,
    array $options = []
  ) {
        $this->userRepository = $userRepository;
        $this->cookieToken = $cookieToken;
        $this->tokenRepository = $tokenRepository;
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
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
        string $password
    ): ResponseInterface {
        $expirationCookie = time() + $this->options['expires'];
        $expirationDate = new \DateTime($expirationCookie);

        $value = $this->cookieToken->getToken($username, $password, $this->salt);
        //['credential', 'random_password', 'expiration_date', 'is_expired']
        $this->tokenRepository->saveToken(
            [
                'credential' => $username, 
                'random_password' => $password, 
                'expiration_date' => $expirationDate, 
                'is_expired' => false
            ]
        );
        $cookie = SetCookie::create($this->options['name'])
            ->withValue($value)
            ->withExpires($expirationCookie)
            ->withPath($this->options['path'])
            ->withDomain(null)
            ->withSecure(false)
            ->withHttpOnly(false);
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param string $secret
     * @return User|null
     */
    public function autoLogin(ServerRequestInterface $request): ?User
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            list($username, $password) = $this->cookieToken->decodeToken($cookie->getValue());
            $user = $this->userRepository->getUser($this->options['field'], $username);
            if ($user && $this->cookieToken->validateToken(
                $cookie->getValue(),
                $username,
                $user->getPassword(),
                $this->salt
            )) {
                return $user;
            }
        }
        return null;
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function onLogout(ServerRequestInterface $request,  ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            $cookie = SetCookie::create($this->options['name'])
                ->withValue('')
                ->withExpires(time() - 3600)
                ->withPath($this->options['path'])
                ->withDomain(null)
                ->withSecure(false)
                ->withHttpOnly(false);
            $response = FigResponseCookies::set($response, $cookie);
        }
        return $response;
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function resume(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            $setCookie = SetCookie::create($this->options['name'])
                ->withValue($cookie->getValue())
                ->withExpires(time() + $this->options['expires'])
                ->withPath($this->options['path'])
                ->withDomain(null)
                ->withSecure(false)
                ->withHttpOnly(false);
            $response = FigResponseCookies::set($response, $setCookie);
        }
        return $response;
    }
}
