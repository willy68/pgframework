<?php

namespace Framework\Middleware;

use Framework\Router;
use Framework\Router\Route;
use GuzzleHttp\Psr7\Response;
use Framework\Router\RouteResult;
use Framework\Invoker\InvokerFactory;
use Invoker\Invoker;
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
     * Injection container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Invoker des callback des routes
     *
     * @var Invoker
     */
    private $invoker;

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
     *
     *
     * @param ContainerInterface $container
     * @param Invoker $invoker
     */
    public function __construct(ContainerInterface $container, Invoker $invoker)
    {
        $this->container = $container;
        $this->invoker = $invoker;
    }

    /**
     * Psr15 middleware process method
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
            $router->middleware($this->routeCallableMiddleware($route, $this->container, $this->invoker));
        }
    }

    /**
     * Wrap route callable controller in middleware
     *
     * @param Route $route
     * @param ContainerInterface $container
     * @param Invoker $invoker
     * @return MiddlewareInterface
     */
    protected function routeCallableMiddleware(
        Route $route,
        ContainerInterface $container,
        Invoker $invoker
    ): MiddlewareInterface
    {
        return new class ($route, $container, $invoker) implements MiddlewareInterface
        {
            /**
             * Route
             *
             * @var Route
             */
            protected $route;

            /**
             * ContainerInterface
             *
             * @var ContainerInterface
             */
            protected $container;

            /**
             * Invoker des callback des route
             *
             * @var Invoker
             */
            protected $invoker;

            /**
             *  constructor.
             * @param Route $route
             * @param ContainerInterface $container
             * @param Invoker $invoker
             */
            public function __construct(Route $route, ContainerInterface $container, Invoker $invoker)
            {
                $this->route = $route;
                $this->container = $container;
                $this->invoker = $invoker;
            }

            /**
             * Psr15 middleware process method
             * @param ServerRequestInterface $request
             * @param RequestHandlerInterface $requestHandler
             * @return ResponseInterface
             */
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                
                $callback = $this->route->getCallback();

                if ($this->container instanceof \DI\Container) {
                    $this->container->set(ServerRequestInterface::class, $request);
                    $response = $this->invoker->call($callback, $this->route->getParams());
                } else {
                    $response = call_user_func_array($callback, [$request]);
                }
        
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
