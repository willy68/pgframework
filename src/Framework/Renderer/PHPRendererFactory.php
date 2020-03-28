<?php

namespace Framework\Renderer;

use Framework\Renderer\PHPRenderer;
use Psr\Container\ContainerInterface;

/**
 * Undocumented class
 */
class PHPRendererFactory
{

    /**
     * Undocumented function
     *
     * @param ContainerInterface $container
     * @return \Framework\Renderer\PHPRenderer
     */
    public function __invoke(ContainerInterface $container): PHPRenderer
    {
        $viewPath = $container->get('views.path');
        return new PHPRenderer($viewPath);
    }
}
