<?php

namespace Framework\Validator\Validation;

use Framework\Validator\ValidationInterface;

class SlugValidation implements ValidationInterface
{
    protected string $pattern = '/^[0-9a-z]+(-[0-9a-z]*)$/';

    protected string $error = "Le champ %s n\est pas un slug valide";

    public function __construct(string $error = null)
    {
        if (!is_null($error)) {
            $this->error = $error;
        }
    }

    /**
     *
     *
     * @param string $param
     * @return self
     */
    public function parseParams(string $param): self
    {
        if (is_string($param)) {
            $this->error = $param;
        }
        return $this;
    }

    /**
     *
     *
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }

    /**
     *
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     *
     *
     * @param mixed$value
     * @return bool
     */
    public function isValid($value): bool
    {
        if (is_null($value) && !preg_match($this->pattern, $value)) {
            return false;
        }
        return true;
    }
}
