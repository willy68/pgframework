<?php

namespace Framework\Auth\RememberMe;

use Framework\Auth\User;
use Dflydev\FigCookies\SetCookie;
use Framework\Auth\Security\Security;
use Psr\Http\Message\ResponseInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Auth\Service\UtilTokenInterface;
use Framework\Auth\Repository\UserRepositoryInterface;
use Framework\Auth\Repository\TokenRepositoryInterface;

class RememberMeDatabase extends RememberMe
{

    /**
     * Token Repository
     *
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    public function __construct(
    UserRepositoryInterface $userRepository,
    UtilTokenInterface $utilToken,
    TokenRepositoryInterface $tokenRepository,
    array $options = []
  ) {
        parent::__construct($userRepository, $utilToken, array_merge(['password_cookie_name' => 'random_password'], $options));
        $this->tokenRepository = $tokenRepository;
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
        string $password
    ): ResponseInterface {
        $response = parent::onLogin($response, $credential, $password);
        $expirationDate = date('Y-m-d H:i:s' , $this->expirationDate);
        //['credential', 'random_password', 'expiration_date', 'is_expired']
        // créé une class Security::randomPassword
        $randomPassword = Security::randomPassword(24);
        $this->tokenRepository->saveToken(
            [
                'credential' => $credential, 
                'random_password' => $randomPassword, 
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
            $dbToken = $this->tokenRepository->getToken($user->$getUserField());
            if ($dbToken) {

            }
        }
        return $user;
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
            list($credential, $password) = $this->utilToken->decodeToken($cookie->getValue());
        }
        $response = parent::onLogout($request, $response);
        /** @todo create TokenRepositoryInterface::updateToken(array $token) */ 
        $this->tokenRepository->saveToken(
            [
                'credential' => $credential, 
                'random_password' => '', 
                'expiration_date' => new \DateTime(-3600), 
                'is_expired' => true
            ]
        );

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
