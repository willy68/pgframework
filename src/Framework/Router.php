<?php

namespace Framework;

use Framework\Router\Route;
use Framework\Router\RouteGroup;
use Mezzio\Router\FastRouteRouter;
use Psr\Container\ContainerInterface;
use Mezzio\Router\Route as MezzioRoute;
use Framework\Middleware\CallableMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Middleware\Stack\MiddlewareAwareStackTrait;
use Framework\Router\RouteCollectionTrait;
use Framework\Router\RouteResult;

/**
 * Register and match route
 */
class Router
{
    use MiddlewareAwareStackTrait;
    use RouteCollectionTrait;
    
    /**
     * DI Container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * method failure match
     *
     * @var array
     */
    private $methodResultFailure = [];

  /**
   * Undocumented variable
   *
   * @var FastRouteRouter
   */
    private $router;

    /**
     * RouteGroup
     *
     * @var RouteGroup[]
     */
    private $groups = [];

    /**
     * Routes a injecter dan le routeur
     *
     * @var Route[]
     */
    private $routes = [];

    /**
     * Inject routes or not
     *
     * @var bool
     */
    private $injected = false;

    /**
     * Router constructor.
     * @param ContainerInterface $c
     * @param string|null $cache
     */
    public function __construct(ContainerInterface $c, ?string $cache = null)
    {
        $this->container = $c;
        $this->router = new FastRouteRouter(null, null, [
            FastRouteRouter::CONFIG_CACHE_ENABLED => !is_null($cache),
            FastRouteRouter::CONFIG_CACHE_FILE => $cache
        ]);
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
        if ($name === null) {
            $name = ($method === null) ? $uri : $uri . '^' . join(':', $method);
        }
        $route = new Route($uri, $callable, $name, $method, []);
        $this->routes[$name] = $route;
        return $route;
    }

    /**
     * Add RouteGroup
     *
     * $router->group('/admin', function (RouteGroup $route) {
     *  $route->addRoute('GET', '/acme/route1', 'AcmeController::actionOne');
     *  $route->addRoute('GET', '/acme/route2', 'AcmeController::actionTwo')->lazyMiddleware(Middleware::class);
     *  $route->addRoute('GET', '/acme/route3', 'AcmeController::actionThree');
     * })
     * ->middleware(Middleware::class);
     *
     * @param string $prefix
     * @param callable $callable
     * @return RouteGroup
     */
    public function group(string $prefix, callable $callable): RouteGroup
    {
        $group = new RouteGroup($prefix, $callable, $this);
        $this->groups[] = $group;

        return $group;
    }

    /**
     * Undocumented function
     *
     * @param string $prefixPath
     * @param string|callable $callable
     * @param string $prefixName
     * @return void
     */
    public function crud(string $prefixPath, $callable, string $prefixName): void
    {
        $this->get("$prefixPath", $callable . '::index', "$prefixName.index");
        $this->get("$prefixPath/new", $callable . '::create', "$prefixName.create");
        $this->post("$prefixPath/new", $callable . '::create');
        $this->get("$prefixPath/{id:\d+}", $callable . '::edit', "$prefixName.edit");
        $this->post("$prefixPath/{id:\d+}", $callable . '::edit');
        $this->delete("$prefixPath/{id:\d+}", $callable . '::delete', "$prefixName.delete");
    }

    /**
   * Undocumented function
   *
   * @param ServerRequestInterface $request
   * @return RouteResult|null
   */
    public function match(ServerRequestInterface $request): ?RouteResult
    {
        $this->processGroups();
        $this->pendingRoutes();

        $result = $this->router->match($request);
        if ($result->isSuccess()) {
            $name = $result->getMatchedRouteName();
            if (array_key_exists($name, $this->routes)) {
                return RouteResult::fromRoute(
                    $this->routes[$name]->setParams($result->getMatchedParams())
                );
            }
            /** @var CallableMiddleware $middleware */
            $middleware = $result->getMatchedRoute()->getMiddleware();
            return RouteResult::fromRoute(
                new Route(
                    $result->getMatchedRoute()->getPath(),
                    $middleware->getCallable(),
                    $name,
                    $result->getMatchedRoute()->getAllowedMethods(),
                    $result->getMatchedParams()
                )
            );
        }
        if ($result->isMethodFailure()) {
            return RouteResult::fromRouteFailure($result->getAllowedMethods());
        }
        return null;
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @param array $params
     * @param array $queryParams
     * @return string
     */
    public function generateUri(string $name, array $params = [], array $queryParams = []): string
    {
        $uri = $this->router->generateUri($name, $params);
        if (!empty($queryParams)) {
            return $uri . '?' . http_build_query($queryParams);
        }
        return $uri;
    }

    /**
     * return method failure match
     *
     * @return bool
     */
    public function isMethodFailure(): bool
    {
        return $this->methodResultFailure['method.failure'] ?? false;
    }

    /**
     * Getter methoFailure
     *
     * @return array
     */
    public function getMethodResultFailure(): array
    {
        return $this->methodResultFailure;
    }

    /**
     * Inject routes in $this->router if not yet
     *
     * @return void
     */
    private function pendingRoutes(): void
    {
        if (!$this->injected) {
            foreach ($this->routes as $route) {
                $this->router->addRoute(
                    new  MezzioRoute(
                        $route->getPath(),
                        new CallableMiddleware($route->getCallback()),
                        $route->getMethod(),
                        $route->getName()
                    )
                );
            }
            $this->injected = true;
        }
    }

    /**
     * Process all groups
     *
     * Adds all of the group routes to the collection and determines if the group
     * strategy should be be used.
     *
     * @return void
     */
    protected function processGroups(): void
    {
        foreach ($this->groups as $key => $group) {
            unset($this->groups[$key]);
            $group();
        }
    }
}
