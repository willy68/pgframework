<?php

namespace Framework\Renderer;

class PHPRenderer implements RendererInterface
{

    /**
     *
     */
    public const DEFAULT_NAMESPACE = '__MAIN';

    /**
     * Undocumented variable
     *
     * @var string[]
     */
    private $paths = [];

    /**
     * Paramètres globale a envoyer à la vue
     *
     * @var array
     */
    private $globals = [];

    /**
     * Undocumented function
     *
     * @param string $defaultPath
     */
    public function __construct(string $defaultPath = null)
    {
        if (!is_null($defaultPath)) {
            $this->addPath($defaultPath);
        }
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
        if (is_null($path)) {
            $this->paths[self::DEFAULT_NAMESPACE] = $namespace;
        } else {
            $this->paths[$namespace] = $path;
        }
    }

    /**
     * Undocumented function
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string
    {
        if ($this->hasNamespace($view)) {
            $path = $this->replaceNamespace($view) . '.php';
        } else {
            $path = $this->paths[self::DEFAULT_NAMESPACE] . DIRECTORY_SEPARATOR . $view . '.php';
        }
        
        ob_start();
        $renderer = $this;
        extract($this->globals);
        extract($params);
        require($path);
        return ob_get_clean();
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
        $this->globals[$key] = $value;
    }

    /**
     * Undocumented function
     *
     * @param string $view
     * @return boolean
     */
    private function hasNamespace(string $view): bool
    {
        return $view[0] === '@';
    }

    /**
     * Undocumented function
     *
     * @param string $view
     * @return string
     */
    private function getNamespace(string $view): string
    {
        return substr($view, 1, strpos($view, '/') - 1);
    }

    /**
     * Undocumented function
     *
     * @param string $view
     * @return string
     */
    private function replaceNamespace(string $view): string
    {
        $namespace = $this->getNamespace($view);
        return str_replace('@' . $namespace, $this->paths[$namespace], $view);
    }
}
