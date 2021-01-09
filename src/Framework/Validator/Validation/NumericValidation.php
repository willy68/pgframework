<?php

namespace Framework\Validator\Validation;

use Framework\Validator\ValidationInterface;

class NumericValidation implements ValidationInterface
{

    protected $error = 'Le champ %s doit être entre %d et %d';

    protected $min;

    protected $max;

    public function __construct(int $min = 1, int $max = 255, string $errormsg = null)
    {
        if ($errormsg !== null) {
            $this->error = $errormsg;
        }
        $this->setMin($min);
        $this->setMax($max);
    }

    public function isValid($var): bool
    {
        return $this->checkNumeric($var);
    }

    public function parseParams($param): self
    {
        if (is_string($param)) {
            list($min, $max, $message) = array_pad(explode(',', $param), 3, '');
            if (!empty($message)) {
                $this->error = $message;
            }
            if (!empty($min)) {
                $this->setMin($min);
            }
            if (!empty($max)) {
                $this->setMax($max);
            }
        }
        return $this;
    }

    public function getParams(): array
    {
        return [$this->min, $this->max];
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

    protected function checkNumeric($var): bool
    {
        if (($val = $this->getNumeric($var)) !== null) {
            $options = array();
            if (!empty($this->min)) {
                $options['options']['min_range'] = $this->min;
            }
            if (!empty($this->max)) {
                $options['options']['max_range'] = $this->max;
            }

            if (($val = filter_var($val, FILTER_VALIDATE_INT, $options)) !== false) {
                return true;
            }
        }
        return false;
    }

    public function setMin($min = 1): self
    {
        $this->min = $this->getNumeric($min);
        return $this;
    }

    public function setMax($max = 255): self
    {
        $this->max = $this->getNumeric($max);
        return $this;
    }

    protected function getNumeric($val)
    {
        if (is_numeric($val)) {
            return $val + 0;
        }
        return null;
    }
}
