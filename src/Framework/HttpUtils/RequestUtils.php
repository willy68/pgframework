<?php

namespace Framework\HttpUtils;

use Psr\Http\Message\ServerRequestInterface;


class RequestUtils
{

    /**
     * Not safe function
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public static function isAjax(ServerRequestInterface $request): bool
    {
        return 'XMLHttpRequest' == $request->getHeader('X-Requested-With');
    }

    /**
     * Return POST params for Ajax call or Normal parsed body
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    public static function getPostParams(ServerRequestInterface $request): array
    {
        if (self::isAjax($request)) {
            return json_decode((string) $request->getBody(), true);
        }
        return $request->getParsedBody();
    }
}
