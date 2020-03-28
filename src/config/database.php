<?php
/**
 * 
 * Exemple
 */

use Psr\Container\ContainerInterface;

return [
    'database.sgdb' => 'mysql',
    'database.host' => 'localhost',
    'database.user' => 'root',
    'database.password' => 'root',
    'database.name' => 'paysagest',
    'database.ajax' => 'communes',
    'ActiveRecord.connections' => function (ContainerInterface $c): array {
        return [
            'development' => $c->get('database.sgdb') . "://" .
                $c->get('database.user') . ":" .
                $c->get('database.password') . "@" .
                $c->get('database.host') . "/" .
                $c->get('database.name') . "?charset=utf8",
            'ajax' => $c->get('database.sgdb') . "://" .
                $c->get('database.user') . ":" .
                $c->get('database.password') . "@" .
                $c->get('database.host') . "/" .
                $c->get('database.ajax') . "?charset=utf8"
        ];
    }
];
