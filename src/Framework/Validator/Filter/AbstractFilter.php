<?php

namespace Framework\Validator\Filter;

abstract class AbstractFilter
{

    /**
     *
     * @Check if POST variable is set
     *
     * @access protected
     *
     * @param string $var variable to check
     * @return bool
     *
     */
    protected function is_set(string $var): bool
    {
        return (bool)(isset($var) && strlen($var));
    }
}
