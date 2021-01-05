<?php

namespace Framework\Router;

use Framework\Middleware\Stack\MiddlewareAwareStackTrait;

/**
 * Class Router
 * Représente une route match
 */
class Route
{
    use MiddlewareAwareStackTrait;

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $path;

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $name;

    /**
     * Undocumented variable
     *
     * @var string[]
     */
    private $method;

    /**
     * Undocumented variable
     *
     * @var string|array|callable
     */
    private $callback;

    /**
     * Undocumented variable
     *
     * @var string[]
     */
    private $params;

    /**
     * parent group
     *
     * @var RouteGroup
     */
    private $group;

    /**
     * Undocumented function
     *
     * @param string $path
     * @param string|array|callable $callback
     * @param string $name
     * @param array|null $method
     * @param array|null $params
     */
    public function __construct(
        string $path,
        $callback,
        ?string $name = null,
        ?array $method = null,
        ?array $params = []
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->callback = $callback;
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * getName
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * getCallback
     *
     * @return string|array|callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * getParams
     *
     * list des paramètres
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @return self
     */
    public function setParams(array $params = []): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string[]
     */
    public function getMethod(): ?array
    {
        return $this->method;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the parent group
     *
     * @return RouteGroup
     */
    public function getParentGroup(): ?RouteGroup
    {
        return $this->group;
    }

    /**
     * Set the parent group
     *
     * @param RouteGroup $group
     *
     * @return Route
     */
    public function setParentGroup(RouteGroup $group): self
    {
        $this->group = $group;
        $prefix      = $this->group->getPrefix();
        $path        = $this->getPath();

        if (strcmp($prefix, substr($path, 0, strlen($prefix))) !== 0) {
            $path = $prefix . $path;
            $this->path = $path;
        }

        return $this;
    }
}
