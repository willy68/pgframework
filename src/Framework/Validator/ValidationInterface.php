<?php

namespace Framework\Validator;

interface ValidationInterface
{

    /**
     * Undocumented function
     *
     * @param mixed $var
     * @return bool
     */
    public function isValid($var): bool;

    /**
     * Get error message
     *
     * @return string
     */
    public function getError(): string;

    /**
     * Parse param rules and error message
     *
     * @param string $param
     * @return self
     */
    public function parseParams(string $param): self;

    /**
     * get param as array
     *
     * @return array
     */
    public function getParams(): array;
}
