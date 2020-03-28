<?php

namespace Framework\Router;

trait RouteCollectionTrait
{

    /**
     * Add a route to the collection
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     * @param array|null $method
     * @return Route
     */
    abstract public function addRoute(string $uri, $callable, ?string $name = null, ?array $method = null): Route;

    /**
     * Add a route that responds to GET HTTP method
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     *
     * @return Route
     */
    public function get(string $uri, $callable, ?string $name = null): Route
    {
        return $this->addRoute($uri, $callable, $name, ['GET']);
    }

    /**
     * Add a route that responds to POST HTTP method
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     *
     * @return Route
     */
    public function post(string $uri, $callable, ?string $name = null): Route
    {
        return $this->addRoute($uri, $callable, $name, ['POST']);
    }

    /**
     * Add a route that responds to PUT HTTP method
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     *
     * @return Route
     */
    public function put(string $uri, $callable, ?string $name = null): Route
    {
        return $this->addRoute($uri, $callable, $name, ['PUT']);
    }

    /**
     * Add a route that responds to PATCH HTTP method
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     *
     * @return Route
     */
    public function patch(string $uri, $callable, ?string $name = null): Route
    {
        return $this->addRoute($uri, $callable, $name, ['PATCH']);
    }

    /**
     * Add a route that responds to DELETE HTTP method
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     *
     * @return Route
     */
    public function delete(string $uri, $callable, ?string $name = null): Route
    {
        return $this->addRoute($uri, $callable, $name, ['DELETE']);
    }

    /**
     * Add a route that responds to HEAD HTTP method
     *
     * @param string $uri
     * @param callable|string $callable
     * @param string|null $name
     * @return Route
     */
    public function head(string $uri, $callable, ?string $name = null): Route
    {
        return $this->addRoute($uri, $callable, $name, ['HEAD']);
    }

    /**
     * Add a route that responds to OPTIONS HTTP method
     *
     * @param string $uri
     * @param string|callable $callable
     * @param string|null $name
     *
     * @return Route
     */
    public function options(string $uri, $callable, ?string $name = null): Route
    {
        return $this->addRoute($uri, $callable, $name, ['OPTIONS']);
    }
}
