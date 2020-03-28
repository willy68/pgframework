<?php

namespace Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CallableMiddleware
 * @package Framework\Middleware
 */
class CallableMiddleware implements MiddlewareInterface
{

  /**
   * Undocumented variable
   *
   * @var string|callable
   */
    private $callable;

    /**
     * Undocumented function
     *
     * @param string|callable $callable
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
    }

    /**
     * Undocumented function
     *
     * @return string|callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
