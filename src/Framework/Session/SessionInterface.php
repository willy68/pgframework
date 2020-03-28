<?php

namespace Framework\Session;

interface SessionInterface
{

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Undocumented function
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * Undocumented function
     *
     * @param string $key
     * @return void
     */
    public function delete(string $key): void;
}
