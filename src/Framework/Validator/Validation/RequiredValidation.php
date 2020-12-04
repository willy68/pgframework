<?php

namespace Framework\Validator\Validation;

use Framework\Validator\ValidationInterface;

class RequiredValidation implements ValidationInterface
{
    protected string $error = 'Le champ %s est obligatoire';

    public function __construct($error = '')
    {
        if (!empty($error)) {
            $this->error = $error;
        }
    }

    public function isValid($var): bool
    {
        return $this->is_set($var);
    }

    public function parseParams($param): self
    {
        return $this;
    }

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
     * @check if POST variable is set
     *
     * @access protected
     *
     * @param string $var The POST variable to check
     * @return bool
     */
    protected function is_set($var): bool
    {
        $check = true;
        if (!isset($var)) {
            $check = false;
        } elseif (is_array($var)) {
            $check = !empty($var);
        } elseif (is_string($var)) { // une chaine constituée que d'espaces est considérée comme vide!
            $check = (bool) strlen(trim($var));
        } else {
            $check = !empty($var); // autre type de variable
        }
        return $check;
    }
}
