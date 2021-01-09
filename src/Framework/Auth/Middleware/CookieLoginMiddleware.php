<?php

namespace Framework\Auth\Middleware;

use Framework\Auth;
use Framework\Auth\ForbiddenException;
use Framework\Auth\RememberMe\RememberMeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CookieLoginMiddleware implements MiddlewareInterface
{
    /**
     *
     * @var Auth
     */
    private $auth;

    /**
     *
     *
     * @var RememberMeInterface
     */
    private $cookie;

    public function __construct(Auth $auth, RememberMeInterface $cookie)
    {
        $this->auth = $auth;
        $this->cookie = $cookie;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->getUser();
        if ($user) {
            return $handler->handle($request);
        }
        $user = $this->cookie->autoLogin($request);
        if (!$user) {
            throw new ForbiddenException("Cookie invalid");
        }
        $this->auth->setUser($user);
        $response = $handler->handle($request);
        return $this->cookie->resume($request, $response);
    }
}
