<?php

namespace Framework\Router;

use Framework\Middleware\Stack\MiddlewareAwareStackTrait;
use Framework\Router;

/**
 * $router->group('/admin', function (RouteGroup $route) {
 * $route->addRoute('GET', '/acme/route1', 'AcmeController::actionOne');
 * $route->addRoute('GET', '/acme/route2', 'AcmeController::actionTwo')->setScheme('https');
 * $route->addRoute('GET', '/acme/route3', 'AcmeController::actionThree');
 * })
 * ->middleware(Middleware::class);
 *
 */
class RouteGroup
{
    use MiddlewareAwareStackTrait;
    use RouteCollectionTrait;

    /**
     * Route prefix for this group
     *
     * @var string
     */
    private $prefix;

    /**
     * Called by router
     *
     * @var callable
     */
    private $callable;

    /**
     * Router
     *
     * @var Router
     */
    private $router;

    /**
     * Construct
     *
     * @param string $prefix
     * @param callable $callable
     * @param Router $router
     */
    public function __construct(string $prefix, callable $callable, Router $router)
    {
        $this->prefix = $prefix;
        $this->callable = $callable;
        $this->router = $router;
    }

    /**
     * Run $callable
     *
     * @return void
     */
    public function __invoke()
    {
        ($this->callable)($this);
    }

    /**
     * Add route
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     * @param array|null $method
     * @return Route
     */
    public function addRoute(string $uri, $callable, ?string $name = null, ?array $method = null): Route
    {
        $path  = ($uri === '/') ? $this->prefix : $this->prefix . sprintf('/%s', ltrim($uri, '/'));
        if ($name === null) {
            $name = ($method === null) ? $uri : $uri . '^' . join(':', $method);
        }
        $route = $this->router->addRoute($path, $callable, $name, $method);

        $route->setParentGroup($this);
        return $route;
    }

    /**
     * Get the value of prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
