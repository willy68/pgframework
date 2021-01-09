<?php

namespace Framework\Validator\Filter;

use Framework\Validator\FilterInterface;

class TrimFilter extends AbstractFilter implements FilterInterface
{
    /**
     * return $var after filter if is set or just $var without filter
     *
     * @param mixed $var
     * @return void
     */
    public function filter($var)
    {
        if ($this->isSet($var)) {
            return trim($var);
        } else {
            return $var;
        }
    }
}
