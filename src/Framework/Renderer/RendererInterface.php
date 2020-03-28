<?php

namespace Framework\Renderer;

interface RendererInterface
{

    /**
     * Undocumented function
     *
     * @param string $namespace
     * @param string $path
     * @return void
     */
    public function addPath(string $namespace, string $path = null);

    /**
     * Undocumented function
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string;

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addGlobal(string $key, $value);
}
