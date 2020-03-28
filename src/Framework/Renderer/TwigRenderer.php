<?php

namespace Framework\Renderer;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Undocumented class
 */
class TwigRenderer implements RendererInterface
{

  /**
   * Undocumented variable
   *
   * @var Environment
   */
    private $twig;

    /**
     * Undocumented function
     *
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Undocumented function
     *
     * @param string $namespace
     * @param string $path
     * @return void
     */
    public function addPath(string $namespace, string $path = null)
    {
        /** @var $loader \Twig\Loader\LoaderInterface */
        $loader = $this->twig->getLoader();
        $loader->addPath($path, $namespace);
    }

    /**
     * Undocumented function
     *
     * @param string $view
     * @param array $params
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    
    public function render(string $view, array $params = []): string
    {
        return $this->twig->render($view . '.twig', $params);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addGlobal(string $key, $value)
    {
        $this->twig->addGlobal($key, $value);
    }

    /**
     * Get twig
     *
     * @return  Environment
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
