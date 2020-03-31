<?php

use Framework\Jwt\JwtMiddlewareFactory;
use Psr\Container\ContainerInterface;
use Tuupola\Http\Factory\ResponseFactory;
use Grafikart\Csrf\CsrfMiddleware;
use Middlewares\Whoops;
use Framework\Twig\{
    CsrfExtension,
    FormExtension,
    TextExtension,
    TimeExtension,
    FlashExtension,
    PagerFantaExtension
};
use Framework\Router;
use Framework\Router\RouterFactory;
use Framework\Router\RouterTwigExtension;
use Framework\Session\PHPSession;
use Framework\Session\SessionInterface;
use Framework\Renderer\RendererInterface;
use Framework\Renderer\TwigRendererFactory;
use Framework\ActiveRecord\ActiveRecordFactory;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Tuupola\Middleware\JwtAuthentication;
use function DI\create;
use function DI\get;
use function DI\factory;
use function DI\env;

return [
    'env' => env('ENV', 'production'),
    'app' => env('APP', 'web'),
    'jwt.secret' => env('JWT_SECRET', 'SecretKey'),
    'twig.extensions' => [
        get(RouterTwigExtension::class),
        get(PagerFantaExtension::class),
        get(TextExtension::class),
        get(TimeExtension::class),
        get(FlashExtension::class),
        get(FormExtension::class),
        get(CsrfExtension::class),
    ],
    SessionInterface::class => create(PHPSession::class),
    CsrfMiddleware::class =>
    create()->constructor(get(SessionInterface::class)),
    JwtAuthentication::class => factory(JwtMiddlewareFactory::class),
    Router::class => factory(RouterFactory::class),
    RendererInterface::class => factory(TwigRendererFactory::class),
    Whoops::class => function (ContainerInterface $c) {
        return new Whoops(null, new ResponseFactory());
    },
    'ActiveRecord' => factory(ActiveRecordFactory::class),
    PDO::class => function (ContainerInterface $c) {
        return new PDO(
            $c->get('database.sgdb') . ":host=" . $c->get('database.host') . ";dbname=" . $c->get('database.name'),
            $c->get('database.user'),
            $c->get('database.password'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ]
        );
    },
    ServerRequestInterface::class => function(ContainerInterface $c) {
        return ServerRequest::fromGlobals();
    }

];
