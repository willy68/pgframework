<?php

namespace Framework\Controller;

use Framework\HttpUtils\RequestUtils;
use Psr\Http\Message\ServerRequestInterface;

class AbstractController
{
    
    /**
     * Return filter Post body from SPA or Web
     *
     * @param ServerRequestInterface $request
     * @param array $filter
     * @return array
     */
    protected function getParams(ServerRequestInterface $request, array $filter): array
    {
        $params = RequestUtils::getPostParams($request);
        if (is_null($params)) {
            return [];
        }
        return array_filter($params, function ($key) use ($filter) {
            return in_array($key, $filter);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Récupère les queryParams de la requète filtrer par les clés
     * du tableau $filter
     *
     * @param ServerRequestInterface $request
     * @param array $options
     * @param array|null $filter default to ['limit', 'offset', 'order', 'include']
     * @return array
     */
    protected function getQueryOption(ServerRequestInterface $request, array $options, ?array $filter = []): array
    {
        if (empty($filter)) {
            $filter = ['limit', 'offset', 'order', 'include'];
        }
        $queryOptions = $request->getQueryParams();
        if (!empty($queryOptions)) {
            array_walk($queryOptions, function ($value, $key) use (&$options, $filter) {
                if (in_array($key, $filter)) {
                    if ($key === 'include') {
                        $options[$key] = [$value];
                    } else {
                        $options[$key] = $value;
                    }
                }
            });
        }
        return $options;
    }

}
