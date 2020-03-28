<?php

namespace Framework\Jwt;

use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;
use Tuupola\Middleware\JwtAuthentication;

class JwtMiddlewareFactory
{

    /**
     * JwtAuthentication Factory
     *
     * @param ContainerInterface $c
     * @return JwtAuthentication
     */
    public function __invoke(ContainerInterface $c): JwtAuthentication
    {
        return new JwtAuthentication([
            'secret' => $c->get('jwt.secret'),
            //'path' => ['/api'],
            //'ignore' => ['/api/user/login'],
            'secure' => false,
            'after' => function ($response, $arguments) use ($c) {
                if (array_key_exists('token', $arguments)) {
                    $jwt = $arguments['token'];
                    try {
                        $jwt = JwtExt::refreshToken($jwt, $c->get('jwt.secret'));
                    } catch (\UnexpectedValueException $e) {
                    }
                    /** @var ResponseInterface $response */
                    $response = $response
                        ->withAddedHeader('Authorization', 'Bearer ' . $jwt)
                        ->withAddedHeader('Access-Control-Expose-Headers', 'Authorization');
                }
                return $response;
            }
        ]);
    }
}
