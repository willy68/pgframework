<?php

namespace Framework\Router;

use Framework\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Undocumented class
 */
class RouterTwigExtension extends AbstractExtension
{

  /**
   * Undocumented variable
   *
   * @var Router
   */
    private $router;

    /**
     * Undocumented function
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'pathFor']),
            new TwigFunction('is_subpath', [$this, 'isSubPath'])
        ];
    }

    /**
     * Undocumented function
     *
     * @param string $path
     * @param array $params
     * @return string
     */
    public function pathFor(string $path, array $params = []): string
    {
        return $this->router->generateUri($path, $params);
    }

    /**
     * Undocumented function
     *
     * @param string $path
     * @return bool
     */
    public function isSubPath(string $path): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $expected = $this->router->generateUri($path);
        return strpos($uri, $expected) !== false;
    }
}
