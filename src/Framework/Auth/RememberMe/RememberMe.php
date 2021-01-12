<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\User;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Framework\Auth\Repository\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Auth\Service\UtilTokenInterface;

class RememberMe implements RememberMeInterface
{
    /**
     * Cookie options
     *
     * @var array
     */
    protected $options = [
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
    protected $userRepository;

    /**
     * Utilitaire de codage et décodage du token
     *
     * @var UtilTokenInterface
     */
    protected $utilToken;

    /**
     * date d'expiration du cookie variable de travail
     *
     * @var int
     */
    protected $expirationDate;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UtilTokenInterface $utilToken,
        array $options = []
    ) {
        $this->userRepository = $userRepository;
        $this->utilToken = $utilToken;
        $this->setOptions($options);
    }

    /**
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
        $value = $this->utilToken->getToken($credential, $password, $salt);

        $this->expirationDate = time() + $this->options['expires'];
        $cookie = SetCookie::create($this->options['name'])
            ->withValue($value)
            ->withExpires($this->expirationDate)
            ->withPath($this->options['path'])
            ->withDomain($this->options['domain'])
            ->withSecure($this->options['secure'])
            ->withHttpOnly($this->options['httpOnly']);
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param string $secret
     * @return User|null
     */
    public function autoLogin(ServerRequestInterface $request, string $salt = ''): ?User
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            list($credential, ) = $this->utilToken->decodeToken($cookie->getValue());
            $user = $this->userRepository->getUser($this->options['field'], $credential);
            if (
                $user && $this->utilToken->validateToken(
                    $cookie->getValue(),
                    $credential,
                    $user->getPassword(),
                    $salt
                )
            ) {
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
    public function onLogout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookie = FigRequestCookies::get($request, $this->options['name']);
        if ($cookie->getValue()) {
            $cookie = SetCookie::create($this->options['name'])
                ->withValue('')
                ->withExpires(time() - 3600)
                ->withPath($this->options['path'])
                ->withDomain($this->options['domain'])
                ->withSecure($this->options['secure'])
                ->withHttpOnly($this->options['httpOnly']);
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
            $this->expirationDate = time() + $this->options['expires'];
            $setCookie = SetCookie::create($this->options['name'])
                ->withValue($cookie->getValue())
                ->withExpires($this->expirationDate)
                ->withPath($this->options['path'])
                ->withDomain($this->options['domain'])
                ->withSecure($this->options['secure'])
                ->withHttpOnly($this->options['httpOnly']);
            $response = FigResponseCookies::set($response, $setCookie);
        }
        return $response;
    }

    /**
     * Modifie le tableau d'options du cookie
     *
     * @param array $options
     * @return RememberMeInterface
     */
    public function setOptions(array $options = []): RememberMeInterface
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        return $this;
    }
}
