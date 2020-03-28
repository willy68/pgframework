<?php

namespace Framework\Database;

use PDO;

/**
 *
 */
class Table
{

    /**
     * Undocumented variable
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $table;

    /**
     * Undocumented variable
     *
     * @var string|null
     */
    protected $entity = \stdClass::class;

    /**
     * Undocumented function
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        if ($pdo) {
            $this->pdo = $pdo;
        }
    }

    /**
     *
     *
     * @return Query
     */
    public function makeQuery(): Query
    {
        return (new Query($this->pdo))
            ->from($this->table, $this->table[0])
            ->into($this->entity);
    }

    /**
     *
     *
     * @return array
     */
    public function findList(): array
    {
        $list = [];
        $results = $this->pdo->query("SELECT id, name FROM {$this->table}")
            ->fetchAll(PDO::FETCH_NUM);
        foreach ($results as $result) {
            $list[$result[0]] = $result[1];
        }
        return $list;
    }

    /**
     * Undocumented function
     *
     * @return Query
     */
    public function findAll(): Query
    {
        return $this->makeQuery();
    }

    /**
     * Undocumented function
     *
     * @param string $field
     * @param string $value
     * @return mixed
     * @throws NoRecordException
     */
    public function findBy(string $field, string $value)
    {
        return $this->makeQuery()->where("$field = :field")->params(["field" => $value])->fetchOrFail();
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @return mixed
     * @throws NoRecordException
     */
    public function find(int $id)
    {
        return $this->makeQuery()->where("id = $id")->fetchOrFail();
    }

    /**
     * Undocumented function
     *
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function update(int $id, array $params): bool
    {
        $fieldsQuery = $this->buildFieldQuery($params);
        $params['id'] = $id;
        $statement = $this->pdo->prepare("UPDATE {$this->table} SET $fieldsQuery WHERE id=:id");
        return $statement->execute($params);
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @return bool
     */
    public function insert(array $params): bool
    {
        $fields = array_keys($params);
        $values = join(',', array_map(function ($field) {
            return ':' . $field;
        }, $fields));
        $query = "INSERT INTO {$this->table} ("
            .  join(',', $fields)
            . ") VALUES ("
            . $values
            . ")";
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    /**
     * Undocumented function
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id=?");
        return $statement->execute([$id]);
    }

    /**
     * Undocumented function
     *
     * @param mixed $id
     * @return bool
     */
    public function exists($id): bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE id=?");
        $statement->execute([$id]);
        return $statement->fetchColumn() !== false;
    }

    /**
     * Undocumented function
     *
     * @return mixed
     */
    public function count()
    {
        return $this->makeQuery()->count();
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @return string
     */
    private function buildFieldQuery(array $params): string
    {
        return join(", ", array_map(function ($field) {
            return "$field=:$field";
        }, array_keys($params)));
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get undocumented variable
     *
     * @return  PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Undocumented function
     *
     * @param string $query
     * @param array $params
     * @return mixed
     * @throws NoRecordException
     */
    protected function fetchOrFail(string $query, array $params = [])
    {
        $query = $this->pdo->prepare($query);
        $query->execute($params);
        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }
        $record = $query->fetch();
        if ($record === false) {
            throw new NoRecordException('');
        }
        return $record;
    }

        /**
     * Undocumented function
     *
     * @param string $query
     * @param array $params
     * @return mixed
     */
    protected function fetchColumn(string $query, array $params = [])
    {
        $query = $this->pdo->prepare($query);
        $query->execute($params);
        if ($this->entity) {
            $query->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }
        return $query->fetchColumn();
    }
}
