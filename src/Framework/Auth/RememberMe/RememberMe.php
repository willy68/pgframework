<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\User;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Framework\Auth\Repository\UserRepository;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Auth\Service\AuthSecurityToken;

class RememberMe implements RememberMeInterface
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
     * User Provider
     * 
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository, array $options = [])
    {
        $this->userRepository = $userRepository;
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
    ): ResponseInterface
    {
        $value = AuthSecurityToken::generateSecurityToken(
            $username,
            $password,
            $this->salt);

        $cookie = SetCookie::create($this->options['name'])
            ->withValue($value)
            ->withExpires(time() + $this->options['expires'])
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
            list($username, $password) = AuthSecurityToken::decodeSecurityToken($cookie->getValue());
            $user = $this->userRepository->getUser($this->options['field'], $username);
            if ($user && AuthSecurityToken::validateSecurityToken(
                        $cookie->getValue(),
                        $username,
                        $user->getPassword(),
                        $this->salt
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
