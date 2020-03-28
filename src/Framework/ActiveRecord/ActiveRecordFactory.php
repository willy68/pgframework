<?php

namespace Framework\ActiveRecord;

use ActiveRecord;
use Psr\Container\ContainerInterface;

class ActiveRecordFactory
{

    /**
     * Initialize ActiveRecord Factory
     *
     * @param ContainerInterface $c
     * @return bool
     */
    public function __invoke(ContainerInterface $c): bool
    {
        ActiveRecord\Config::initialize(function ($cfg) use ($c) {
            $cfg->set_connections(
                $c->get('ActiveRecord.connections')
            );

            // default connection is now development
            $cfg->set_default_connection('development');
        });
        return true;
    }
}
