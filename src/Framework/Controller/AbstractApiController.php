<?php

namespace Framework\Controller;

use ActiveRecord\Model;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AbstractApiController extends AbstractController
{

    /**
     * Model class
     *
     * @var string
     */
    protected $model = Model::class;

    /**
     * Attributs du model
     *
     * @var array
     */
    protected $attributes = [];

    /**
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * AbstractApiController constructor.
     */
    public function __construct()
    {
        $this->attributes = $this->getModelAttributes();
    }

    /**
     * Get list of record
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $options = [];
        $attributes = $request->getAttributes();

        if (!empty($this->foreignKey) && (isset($attributes[$this->foreignKey]))) {
            $options['conditions'] = [$this->foreignKey . ' = ?', $attributes[$this->foreignKey]];
        }

        $options = $this->getQueryOption($request, $options);
        try {
            if (!empty($options)) {
                $models = $this->model::all($options);
            } else {
                $models = $this->model::all();
            }
        } catch (\ActiveRecord\RecordNotFound $e) {
            return new Response(404);
        }
        if (empty($models)) {
            return new Response(404);
        }
        $json = $this->jsonArray(
            $models,
            isset($options['include']) ? ['include' => $options['include']] : []
        );
        return new Response(200, [], $json);
    }

    /**
     * Get model by id
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $options = [];
        $options = $this->getQueryOption($request, $options);
        $id = $request->getAttribute('id', 0);
        try {
            $model = $this->model::find($id);
        } catch (\ActiveRecord\RecordNotFound $e) {
            return new Response(404);
        }
        return new Response(
            200,
            [],
            $model->to_json(isset($options['include']) ? ['include' => $options['include']] : [])
        );
    }
    /**
     * Update model by id
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id', 0);
        try {
            $model = $this->model::find($id);
        } catch (\ActiveRecord\RecordNotFound $e) {
            return new Response(404);
        }
        $params = $this->getParams($request, $this->attributes);
        if ($model->update_attributes($params)) {
            return new Response(200, [], $model->to_json());
        } else {
            return new Response(400);
        }
    }

    /**
     * Create record
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Get body params
        $params = $this->getParams($request, $this->attributes);

        // Get route params
        if (!empty($this->foreignKey)) {
            if ($request->getAttribute($this->foreignKey)) {
                $params[$this->foreignKey] = $request->getAttribute($this->foreignKey);
            }
        }
        $model = new $this->model();
        $model->set_attributes($params);

        if ($model->save()) {
            return (new Response(200, [], $model->to_json()));
        } else {
            return new Response(400);
        }
    }

    /**
     * Delete model by id
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id', 0);

        try {
            $model = $this->model::find($id);
        } catch (\ActiveRecord\RecordNotFound $e) {
            return new Response(404);
        }

        if ($model->delete()) {
            return new Response(200);
        } else {
            return new Response(400);
        }
    }

    /**
     * Get fields from model
     *
     * @param array $filter if empty use ['id', 'created_at', 'updated_at']
     * qui sont gérées par ActiveRecord
     * @return array
     */
    protected function getModelAttributes(array $filter = []): array
    {
        if (empty($filter)) {
            $filter = ['id', 'created_at', 'updated_at'];
        }
        return array_filter(
            array_keys((new $this->model())->attributes()),
            function ($field) use ($filter) {
                return !in_array($field, $filter);
            }
        );
    }

    /**
     *
     * Transform le tableau $record
     * en un tableau d'objets json
     *
     * @param array $records
     * @param array $include
     * @return string
     */
    public function jsonArray(array $records, $include = []): string
    {
        $json = join(',', array_map(function ($record) use ($include) {
            return $record->to_json($include);
        }, $records));
        return '[' . $json . ']';
    }
}
