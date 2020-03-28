<?php

namespace Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Framework\Router\RouteResult;
use GuzzleHttp\Psr7\Response;

class ApiOptionsMiddleware implements MiddlewareInterface
{

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() !== RequestMethod::METHOD_OPTIONS) {
            return $handler->handle($request);
        }

        /** @var RouteResult $result */
        $result = $request->getAttribute(RouteResult::class);
        if (!$result) {
            return $handler->handle($request);
        }
        if (!$result->isMethodFailure()) {
            return $handler->handle($request);
        }
        if ($result->getMatchedRoute()) {
            return $handler->handle($request);
        }

        return new Response(200, [
            'Access-Control-Allow-Headers' =>
                'X-CSRF-TOKEN,' .
                ' X-Requested-With,' .
                ' Content-Type,' .
                ' Origin,' .
                ' Authorization,' .
                ' Accept,' .
                ' Client-Security-Token,' .
                ' User-Agent',
            'Access-Control-Allow-Methods' => $result->getAllowedMethods(),
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => 'true',
            'Content-Type' => 'application/json, application/*+json; charset=UTF-8'
        ]);
    }
}
