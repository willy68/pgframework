<?php

namespace Framework\Database;

use Pagerfanta\Adapter\AdapterInterface;

class PaginatedQuery implements AdapterInterface
{

    /**
     * Undocumented variable
     *
     * @var Query
     */
    private $query;

    /**
     * Undocumented function
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getNbResults(): int
    {
        return $this->query->count();
    }

    /**
     * Undocumented function
     *
     * @param [type] $offset
     * @param [type] $length
     * @return QueryResult
     */
    public function getSlice($offset, $length): QueryResult
    {
        $query = clone $this->query;
        return $query->limit($length, $offset)->fetchAll();
    }
}
