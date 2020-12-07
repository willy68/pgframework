<?php

namespace Framework\Middleware;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Framework\Router;
use Framework\Router\Route;
use Framework\Router\RouteResult;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Undocumented class
 */
class DispatcherMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * Undocumented variable
     *
     * @var Container
     */
    private $container;

    /**
     * Router
     *
     * @var Router
     */
    private $router;
    
    /**
     * Next App Handler
     *
     * @var RequestHandlerInterface
     */
    private $next;

    /**
     * Undocumented function
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Undocumented function
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $next
     * @return ResponseInterface
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        /** @var RouteResult $result */
        $result = $request->getAttribute(RouteResult::class);
        if (is_null($result)) {
            return $next->handle($request);
        }
        if ($result->isMethodFailure()) {
            return $next->handle($request);
        }

        $this->next = $next;
        $this->router = $this->container->get(Router::class);
        $this->prepareMiddlewareStack($this->router, $result->getMatchedRoute());

        return $this->handle($request);
    }

    /**
     * Internal routing Middleware handler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->router->shiftMiddleware($this->container);

        if (is_null($middleware)) {
            // return App handler;
            return $this->next->handle($request);
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        } elseif (is_callable($middleware)) {
            return call_user_func_array($middleware, [$request, [$this, 'handle']]);
        }
        return $this->next->handle($request);
    }

    /**
     * Prepare la pile de middleware du router
     *
     * @param Router $router
     * @param Route|null $route
     * @return void
     */
    protected function prepareMiddlewareStack(Router $router, ?Route $route): void
    {
        if ($route) {
            /** router stack first */
            if ($this->container->has('router.middlewares')) {
                $router->middlewares($this->container->get('router.middlewares'));
            }
            /** route group stack second */
            if ($group = $route->getParentGroup()) {
                foreach ($group->getMiddlewareStack() as $middleware) {
                    $router->middleware($middleware);
                }
            }
            /** route stack */
            foreach ($route->getMiddlewareStack() as $middleware) {
                $router->middleware($middleware);
            }
            /** wrap route callable end */
            $router->middleware($this->routeCallableMiddleware($route, $this->container));
        }
    }

    /**
     * Wrap route callable controller in middleware
     *
     * @param Route $route
     * @param ContainerInterface $container
     * @return MiddlewareInterface
     */
    protected function routeCallableMiddleware(Route $route, ContainerInterface $container): MiddlewareInterface
    {
        return new class ($route, $container) implements MiddlewareInterface
        {
            /**
             * Route
             *
             * @var Route
             */
            protected $route;

            /**
             * Container DI
             *
             * @var Container
             */
            protected $container;

            /**
             *  constructor.
             * @param Route $route
             * @param ContainerInterface $container
             */
            public function __construct(Route $route, ContainerInterface $container)
            {
                $this->route = $route;
                $this->container = $container;
            }

            /**
             * @param ServerRequestInterface $request
             * @param RequestHandlerInterface $requestHandler
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                
                $callback = $this->route->getCallback();

                if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
                    $callback = [$this->container->get($callback[0]), $callback[1]];
                }
        
                if (is_string($callback) && method_exists($callback, '__invoke')) {
                    $callback = $this->container->get($callback);
                }
        
                if (is_string($callback)) {
                    $callback = $this->container->get($callback);
                }
        
                if (! is_callable($callback)) {
                    throw new \InvalidArgumentException('Could not resolve a callback for this route');
                }

                //$params = array_merge(['request' => $request], $this->route->getParams());
                //$response = $this->container->call($callback, $params);
                $this->container->set(ServerRequestInterface::class, $request);
                $response = $this->container->call($callback, $this->route->getParams());
        
                if (is_string($response)) {
                    return new Response(200, [], $response);
                } elseif ($response instanceof ResponseInterface) {
                    return $response;
                } else {
                    throw new \Exception('The response is not a string or a ResponseInterface');
                }
                return $requestHandler->handle($request);
            }
        };
    }
}
