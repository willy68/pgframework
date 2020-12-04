<?php

namespace Framework\Database\ActiveRecord;

use ActiveRecord;
use Pagerfanta\Pagerfanta;
use Framework\Database\Query;
use Framework\Database\ActiveRecord\PaginatedActiveRecord;

class PaginatedModel extends ActiveRecord\Model
{
 
    public static $paginatedCondition = [];

    public static $query;

    /**
     * Undocumented function
     *
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     */
    public static function paginate(int $perPage, int $currentPage = 1): Pagerfanta
    {
        $paginator = new PaginatedActiveRecord(static::class);
        return (new Pagerfanta($paginator))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public static function getNbResults(): int
    {
        $options = null;
        if (!empty(static::$paginatedCondition['conditions'])) {
            $options['conditions'] = static::$paginatedCondition['conditions'];
        }
        return static::count($options);
    }

    /**
     * Undocumented function
     *
     * @param int $offset
     * @param int $length
     * @return mixed
     */
    public static function paginatedQuery(int $offset, int $length)
    {
        static::$paginatedCondition['limit'] = $length;
        static::$paginatedCondition['offset'] = $offset;
        return static::find('all', static::$paginatedCondition);
    }

    /**
     * set paginated options conditions
     *
     * @param \Framework\Database\Query $query
     * @return string static::class
     */
    public static function setPaginatedQuery(Query $query): string
    {
        if (!empty($where = $query->getWhere())) {
            static::$paginatedCondition['conditions'] = [$where];
        }
        if (!empty($order = $query->getOrder())) {
            static::$paginatedCondition['order'] = $order;
        }
        return static::class;
    }

    /**
     * Init options conditions for all Post
     *
     * @return Query
     */
    public static function findAll(): Query
    {
        return static::makeQuery();
    }

    /**
     *
     *
     * @param array $field
     * @return array
     */
    public static function findList(array $field): array
    {
        return static::find('all', ['select' => join(", ", $field)]);
    }

    /**
     * Init query
     *
     * @return \Framework\Database\Query
     */
    public static function makeQuery(): Query
    {
        if (!static::$query) {
            return static::$query = new Query();
        }
        return static::$query;
    }
}
