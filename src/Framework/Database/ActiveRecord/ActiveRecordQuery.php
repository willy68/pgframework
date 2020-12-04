<?php

namespace Framework\Database\ActiveRecord;

class ActiveRecordQuery
{

    private $options = [];

    private $whereValue = [];

    private $select;

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $from = [];

    private $where = [];

    private $group;

    private $order = [];

    private $limit;

    private $joins = [];

    private $params = [];

    /**
     * ActiveRecordQuery constructor
     */
    public function __construct()
    {
    }

    /**
     * Undocumented function
     *
     * @param string $table
     * @param string|null $alias
     * @return self
     */
    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }
        $this->options['from'] = $this->buildFrom();
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string[] ...$fields
     * @return self
     */
    public function select(string ...$fields): self
    {
        $this->options['select'] = join(', ', $fields);
        return $this;
    }

    /**
     *
     * @param string[] ...$condition
     * @return self
     */
    public function where(string ...$condition): self
    {
        if (empty($this->options['conditions'])) {
            $this->options['conditions'] = [];
        }
        $this->options['conditions'] = [
            join(
                ' AND ',
                array_merge($this->options['conditions'], $condition)
            )
        ];
        return $this;
    }

    /**
     *
     * @param string[] ...$condition
     * @return self
     */
    public function orWhere(string ...$condition): self
    {
        if (empty($this->options['conditions'])) {
            $this->options['conditions'] = [];
        }
        $this->options['conditions'] = [
            join(
                ' OR ',
                array_merge($this->options['conditions'], $condition)
            )
        ];
        return $this;
    }

    /**
     * - Ajoute les valeur au tableau conditions
     * - A ne faire qu'après toutes les conditions
     *
     * @param array $whereValue
     * @return self
     */
    public function setWhereValue(array $whereValue): self
    {
        $this->whereValue = array_merge($this->whereValue, $whereValue);
        $this->options['conditions'] = array_merge($this->options['conditions'], $this->whereValue);
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param $length
     * @param int $offset
     * @return self
     */
    public function limit($length, $offset = 0): self
    {
        $this->options['limit'] = (int) $length;
        $this->options['offset'] = (int) $offset;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param int $offset
     * @return self
     */
    public function offset($offset = 0): self
    {
        $this->options['offset'] = (int) $offset;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $orders
     * @return self
     */
    public function order(string $orders): self
    {
        $this->options['order'] = $orders;
        return $this;
    }

    /**
     *
     * @param string $group
     * @return self
     */
    public function group(string $group): self
    {
        $this->options['group'] = "GROUP BY $group";
        return $this;
    }

    /**
     *
     * @param string $having
     * @return self
     */
    public function having(string $having): self
    {
        $this->options['having'] = "HAVING $having";
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string|array $table
     * @param string|null $condition
     * @param string $type
     * @return self
     */
    public function join($table, ?string $condition = null, string $type = 'LEFT'): self
    {
        if (is_array($table)) {
            $this->options['joins'] = $table;
        } else {
            $this->joins[$type][] = [$table, $condition, $type];
            $this->options['joins'] = $this->buildJoins();
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @return self
     */
    public function params(array $params): self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function __toString()
    {
        $parts = ['SELECT'];
        if ($this->select) {
            $parts[] = join(', ', $this->select);
        } else {
            $parts[] = '*';
        }
        $parts[] = 'FROM';
        $parts[] = $this->buildFrom();
        if (!empty($this->joins)) {
            foreach ($this->joins as $type => $joins) {
                foreach ($joins as [$table, $condition]) {
                    $parts[] = strtoupper($type) . " JOIN $table ON $condition";
                }
            }
        }
        if (!empty($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = "(" . join(') AND (', $this->where) . ")";
        }
        if (!empty($this->order)) {
            $parts[] = 'ORDER BY';
            $parts[] = join(', ', $this->order);
        }
        if ($this->limit) {
            $parts[] = 'LIMIT ' . $this->limit;
        }

        return join(' ', $parts);
    }

    /**
     * get Where conditions
     *
     * @return string
     */
    public function getWhere(): string
    {
        if (!empty($this->where)) {
            return "(" . join(') AND (', $this->where) . ")";
        }
        return '';
    }

    /**
     * get Order conditions
     *
     * @return string
     */
    public function getOrder(): string
    {
        if (!empty($this->order)) {
            return join(', ', $this->order);
        }
        return '';
    }

    /**
     * get Limit conditions
     *
     * @return string
     */
    public function getLimit(): string
    {
        if ($this->limit) {
            return $this->limit;
        }
        return '';
    }

    /**
     * Build FROM string
     *
     * @return string
     */
    private function buildFrom(): string
    {
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$key as $value";
            } else {
                $from[] = $value;
            }
        }
        return join(', ', $from);
    }

    private function buildJoins()
    {
        $parts = [];
        foreach ($this->joins as $type => $joins) {
            foreach ($joins as [$table, $condition]) {
                $parts[] = strtoupper($type) . " JOIN $table ON $condition";
            }
        }
        return join(' ', $parts);
    }
}
