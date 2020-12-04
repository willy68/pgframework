<?php

namespace Framework\Validator\Validation;

use Psr\Http\Message\UploadedFileInterface;
use Framework\Validator\ValidationInterface;

class ExtensionValidation implements ValidationInterface
{

    private const MIME_TYPES = [
        'jpg' => 'image/jpg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'ico' => 'image/ico',
        'pdf' => 'application/pdf'
    ];

    protected array $extensions = [];

    protected string $error = "Le champ %s n'est pas au format valide (%s)";

    /**
     * ExtensionValidation constructor.
     * @param array $extensions
     * @param string|null $error
     */
    public function __construct(array $extensions = [], string $error = null)
    {
        if (!is_null($error)) {
            $this->error = $error;
        }
        $this->extensions = $extensions;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function parseParams(string $param): self
    {
        if (is_string($param)) {
            list($ext, $msg) = explode(']', $param);
            $ext = substr($ext, 1);
            $this->extensions = (explode(',', $ext)) ?: [];
            if (!empty($msg)) {
                $this->error = substr($msg, 1);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [join(',', $this->extensions)];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param mixed $file
     * @return bool
     */
    public function isValid($file): bool
    {
        /** @var UploadedFileInterface $file */
        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $type = $file->getClientMediaType();
            $extension = mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType = self::MIME_TYPES[$extension] ?? null;
            if (!in_array($extension, $this->extensions) || $expectedType !== $type) {
                return false;
            }
        }
        return true;
    }
}
