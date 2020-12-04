<?php

namespace Framework\Validator\Validation;

use Framework\Validator\ValidationInterface;
use Psr\Http\Message\ServerRequestInterface;

class EmailConfirmValidation implements ValidationInterface
{
    protected string $error = 'Le champ %s doit être un E-mail identique avec le champ %s';

    protected string $fieldName;

    protected $params = [];

    /**
     * EmailConfirmValidation constructor.
     * @param ServerRequestInterface $request
     * @param string|null $fieldName
     * @param string|null $error
     */
    public function __construct(
        ServerRequestInterface $request,
        string $fieldName = null,
        string $error = null
    ) {
        if ($error !== null) {
            $this->error = $error;
        }
        $this->params = $request->getParsedBody();
        $this->setFieldName($fieldName);
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public function isValid($var): bool
    {
        if ($this->checkField($var)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $param
     * @return $this
     */
    public function parseParams($param): self
    {
        if (is_string($param)) {
            list($fieldName, $message) = array_pad(explode(',', $param), 2, '');
            if (!empty($message)) {
                $this->error = $message;
            }
            if (!empty($fieldName)) {
                $this->setFieldName($fieldName);
            }
        }
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getParams(): array
    {
        return [$this->fieldName];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $var
     * @return bool
     */
    protected function checkField(string $var): bool
    {
        if (is_string($var)) {
            if (isset($this->params[$this->fieldName])) {
                return $this->params[$this->fieldName] === $var;
            }
        }
        return false;
    }

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName(string $fieldName): self
    {
        if (is_string($fieldName)) {
            $this->fieldName = $fieldName;
        }
        return $this;
    }
}
