<?php

namespace Framework\Router;

use Framework\Router;
use Psr\Container\ContainerInterface;

class RouterFactory
{

    /**
     * @param ContainerInterface $c
     * @return Router
     */
    public function __invoke(ContainerInterface $c)
    {
        $cache = null;
        if ($c->get('env') === 'production') {
            $cache = 'tmp/route';
        }
        return new Router($c, $cache);
    }
}
