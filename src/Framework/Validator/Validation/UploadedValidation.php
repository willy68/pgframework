<?php

namespace Framework\Validator\Validation;

use Psr\Http\Message\UploadedFileInterface;
use Framework\Validator\ValidationInterface;

class UploadedValidation implements ValidationInterface
{
    protected string $error = "Le champ %s n'est pas au format valide (%s)";

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
     * @param mixed $file
     * @return bool
     */
    public function isValid($file): bool
    {
        /** @var UploadedFileInterface $file */
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return false;
        }
        return true;
    }
}
