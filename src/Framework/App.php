<?php

namespace Framework;

use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Framework\Middleware\Stack\MiddlewareAwareStackTrait;

/**
 * Application
 */
class App implements RequestHandlerInterface
{
    use MiddlewareAwareStackTrait;

    /**
     *
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $config = [];

    /**
     * Undocumented modules
     *
     * @var array
     */
    private $modules = [];

    /**
     * App constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config[] = dirname(__DIR__ ) . '/config/config.php';
        $this->config = \array_merge($this->config, $config);
    }

    /**
     * Undocumented function
     *
     * @param string $module
     * @return self
     */
    public function addModule(string $module): self
    {
        $this->modules[] = $module;
        return $this;
    }

        /**
         * Undocumented function
         *
         * @param array $modules
         * @return self
         */
        public function addModules(array $modules): self
        {
            foreach ($modules as $module) {
                $this->addModule($module);
            }
            return $this;
        }

    /**
     * Undocumented function
     *
     * @param string $routePrefix
     * @param string|null $middleware
     * @return self
     */
    public function pipe(string $routePrefix, ?string $middleware = null): self
    {
        /** MiddlewareAwareStackTrait::lazyPipe */
        return $this->lazyPipe($routePrefix, $middleware, $this->getContainer());
    }

    /**
     * Undocumented function
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getMiddleware();
        if (is_null($middleware)) {
            throw new Exception('Aucun middleware n\'a intercepté cette requête');
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        } elseif (is_callable($middleware)) {
            return call_user_func_array($middleware, [$request, [$this, 'handle']]);
        }
    }

    /**
     * Undocumented function
     *
     * @param  ServerRequestInterface|null $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface
    {
        if ($request === null) {
            $request = $this->getContainer()->get(ServerRequestInterface::class);
        }
        foreach ($this->modules as $module) {
            $this->getContainer()->get($module);
        }

        return $this->handle($request);
    }

    /**
     * Undocumented function
     *
     * @return ContainerInterface
     * @throws Exception
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $builder = new ContainerBuilder();
            $env = getenv('ENV') ?: 'production';
            if ($env === 'production') {
                $builder->enableCompilation('tmp/di');
                $builder->writeProxiesToFile(true, 'tmp/proxies');
            }
            foreach ($this->config as $config) {
                $builder->addDefinitions($config);
            }
            foreach ($this->modules as $module) {
                if ($module::DEFINITIONS) {
                    $builder->addDefinitions($module::DEFINITIONS);
                }
            }
            $this->container = $builder->build();
        }
        return $this->container;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Undocumented function
     *
     * @return object
     * @throws Exception
     */

    private function getMiddleware()
    {
        return $this->shiftMiddleware($this->getContainer());
    }
}
