<?php

namespace Framework\Database\ActiveRecord;

use Pagerfanta\Adapter\AdapterInterface;

class PaginatedActiveRecord implements AdapterInterface
{
    protected $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getNbResults(): int
    {
        return $this->model::getNbResults();
    }

    /**
     * Undocumented function
     *
     * @param int $offset
     * @param int $length
     * @return \ActiveRecord\Model[]
     */
    public function getSlice($offset, $length)
    {
        return $this->model::paginatedQuery($offset, $length);
    }
}
