<?php

namespace Framework\Validator\Filter;

use Framework\Validator\FilterInterface;

class StriptagsFilter extends AbstractFilter implements FilterInterface
{
    
    /**
     *
     *
     * @param mixed $var
     * @return mixed
     */
    public function filter($var)
    {
        if ($this->is_set($var)) {
            return strip_tags($var);
        } else {
            return $var;
        }
    }
}
