<?php

namespace Framework\Renderer;

class JsonRenderer implements RendererInterface
{

    /**
     * Render as json
     *
     * @param mixed $view
     * @param array|int|null $options
     * @return string
     */
    public function render($view, $options = null): string
    {
        return $this->toJson($view, $options);
    }

    /**
     *
     *
     * @param mixed $view
     * @param array|int|null $options
     * @return void
     */
    public function toJson($view, $options): string
    {
        if ($view instanceof \ActiveRecord\Model) {
            if (!is_array($options)) {
                $options = [$options];
            }
            return $view->to_json($options);
        } elseif (is_array($view)) {
            return $this->jsonArray($view, $options);
        } else {
            return json_encode($view, $options);
        }
    }

    public function jsonArray(array $view, $options): string
    {
        if (!empty($view) && $view[0] instanceof \ActiveRecord\Model) {
            if (!is_array($options)) {
                $options = [$options];
            }
            return $this->jsonRecordArray($view, $options);
        } else {
            return json_encode($view, $options);
        }
    }

    /**
     *
     * Transform le tableau $record (\ActiveRecord\Model)
     * en un tableau d'objets json
     *
     * @param array $records
     * @param array $include
     * @return string
     */
    public function jsonRecordArray(array $records, array $include = []): string
    {
        $json = join(',', array_map(function ($record) use ($include) {
            /** @var  \ActiveRecord\Model $record */
            return $record->to_json($include);
        }, $records));
        return '[' . $json . ']';
    }

    /**
     * Unused function
     *
     * @param string $namespace
     * @param string $path
     * @return void
     */
    public function addPath(string $namespace, string $path = null)
    {
    }

    /**
     * Unused function
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addGlobal(string $key, $value)
    {
    }
}
