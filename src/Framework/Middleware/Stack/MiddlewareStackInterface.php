<?php

/**
 * https://github.com/thephpleague/route
 */

declare(strict_types=1);

namespace Framework\Middleware\Stack;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareStackInterface
{
    /**
     * Add a middleware to the stack
     *
     * @param MiddlewareInterface $middleware
     *
     * @return static
     */
    public function middleware(MiddlewareInterface $middleware): self;

    /**
     * Add multiple middleware to the stack
     *
     * @param MiddlewareInterface[] $middlewares
     *
     * @return static
     */
    public function middlewares(array $middlewares): self;

    /**
     * Prepend a middleware to the stack
     *
     * @param MiddlewareInterface $middleware
     *
     * @return static
     */
    public function prependMiddleware(MiddlewareInterface $middleware): self;

    /**
     * Shift a middleware from beginning of stack
     *
     * @return MiddlewareInterface|null
     */
    public function shiftMiddleware(): MiddlewareInterface;

    /**
     * Get the stack of middleware
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewareStack(): iterable;
}
