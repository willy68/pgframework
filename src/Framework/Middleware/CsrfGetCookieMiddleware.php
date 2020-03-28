<?php

namespace Framework\Middleware;

use Grafikart\Csrf\CsrfMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfGetCookieMiddleware implements MiddlewareInterface
{

    /**
     * Undocumented variable
     *
     * @var CsrfMiddleware
     */
    private $csrfMiddleware;

    /**
     * CsrfGetCookieMiddleware constructor.
     * @param CsrfMiddleware $csrfMiddleware
     */
    public function __construct(CsrfMiddleware $csrfMiddleware)
    {
        $this->csrfMiddleware = $csrfMiddleware;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (\in_array($request->getMethod(), ['DELETE', 'PATCH', 'POST', 'PUT'], true)) {
            if (!$request->hasHeader('X-CSRF-TOKEN')) {
                return $handler->handle($request);
            }
                $params = $request->getParsedBody() ?: [];
                $token = $request->getHeader('X-CSRF-TOKEN')[0];
                $request = $request->withParsedBody(
                    array_merge($params, [$this->csrfMiddleware->getFormKey() => $token])
                );
        }
        return $handler->handle($request);
    }
}
