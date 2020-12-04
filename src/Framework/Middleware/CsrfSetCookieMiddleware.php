<?php

namespace Framework\Middleware;

use Dflydev\FigCookies\SetCookie;
use Exception;
use Grafikart\Csrf\CsrfMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfSetCookieMiddleware implements MiddlewareInterface
{

    /**
     * Undocumented variable
     *
     * @var CsrfMiddleware
     */
    private $csrfMiddleware;

    /**
     * CsrfSetCookieMiddleware constructor.
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
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (\in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            $response = $handler->handle($request);
            if (!FigResponseCookies::get($response, 'XSRF-TOKEN')->getValue()) {
                $setCookie = SetCookie::create('XSRF-TOKEN')
                    ->withValue($this->csrfMiddleware->generateToken())
                    // ->withExpires(time() + 3600)
                    ->withPath('/')
                    ->withDomain(null)
                    ->withSecure(false)
                    ->withHttpOnly(false);
                $response = FigResponseCookies::set($response, $setCookie);
            }
            return $response;
        }
        return $handler->handle($request);
    }
}
