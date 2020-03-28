<?php

namespace Framework\Database;

class QueryResult implements \ArrayAccess, \Iterator
{

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $records;

    /**
     * Undocumented variable
     *
     * @var mixed
     */
    private $entity;

    /**
     * Undocumented variable
     *
     * @var int
     */
    private $index = 0;

    private $hydratedRecords = [];

    /**
     *
     *
     * @param array $records
     * @param string|null $entity
     */
    public function __construct(array $records, ?string $entity = null)
    {
        $this->records = $records;
        $this->entity = $entity;
    }

    /**
     * Undocumented function
     *
     * @param int $index
     * @return mixed
     */
    public function get(int $index)
    {
        if ($this->entity) {
            if (!isset($this->hydratedRecords[$this->index])) {
                $this->hydratedRecords[$this->index] = Hydrator::hydrate($this->records[$index], $this->entity);
            }
            return $this->hydratedRecords[$this->index];
        }
        return $this->entity;
    }

    /**
     * @inheritDoc
     *
     */
    public function offsetExists($offset): bool
    {
        return isset($this->records[$offset]);
    }

    /**
     * @inheritDoc
     *
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     *
     */
    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Can\'t alter records');
    }

    /**
     * @inheritDoc
     *
     */
    public function offsetUnset($offset): void
    {
        throw new \Exception('Can\'t alter records');
    }

    /**
     * @inheritDoc
     *
     */
    public function current()
    {
        return $this->get($this->index);
    }

    /**
     * @inheritDoc
     *
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     *
     */
    public function next()
    {
        $this->index ++;
    }

    /**
     * @inheritDoc
     *
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     *
     */
    public function valid(): bool
    {
        return isset($this->records[$this->index]);
    }
}
