<?php

namespace Framework;

use Framework\Database\Table;
use Framework\Validator\ValidationError;
use PDO;

class Validator
{

    private const MIME_TYPES = [
        'jpg' => 'image/jpg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'ico' => 'image/ico',
        'pdf' => 'application/pdf'
    ];

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $params;

    /**
     * @var ValidationError[]
     */
    private $errors = [];

    /**
     * Validator constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Undocumented function
     *
     * @param string[] $keys
     * @return self
     */
    public function required(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value)) {
                $this->addError($key, 'required');
            }
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string[] $keys
     * @return self
     */
    public function notEmpty(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value) || empty($value)) {
                $this->addError($key, 'empty');
            }
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param int|null $min
     * @param int|null $max
     * @return self
     */
    public function length(string $key, ?int $min, ?int $max = null): self
    {
        $value = $this->getValue($key);
        $len = mb_strlen($value);
        if (
            !is_null($min) &&
            !is_null($max) &&
            ($len < $min || $len > $max)
        ) {
            $this->addError($key, 'betweenLength', [$min, $max]);
            return $this;
        }
        if (
            !is_null($min) && $len < $min
        ) {
            $this->addError($key, 'minLength', [$min]);
            return $this;
        }
        if (
            !is_null($max) && $len > $max
        ) {
            $this->addError($key, 'maxLength', [$max]);
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @return self
     */
    public function slug(string $key): self
    {
        $value = $this->getValue($key);
        $pattern = '/^[0-9a-z]+(-[0-9a-z]*)$/';
        if (is_null($value) && !preg_match($pattern, $value)) {
            $this->addError($key, 'slug');
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $format
     * @return $this
     */
    public function dateTime(string $key, string $format = 'Y-m-d H:i:s'): self
    {
        $value = $this->getValue($key);
        $datetime = \DateTime::createFromFormat($format, $value);
        $errors = \DateTime::getLastErrors();
        if (
            $errors['error_count'] > 0 ||
            $errors['warning_count'] > 0 ||
            $datetime === false
        ) {
            $this->addError($key, 'datetime', [$format]);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param string $table
     * @param PDO $pdo
     * @return self
     */
    public function exists(string $key, string $table, PDO $pdo): self
    {
        $id = $this->getValue($key);
        $statement = $pdo->prepare("SELECT id FROM {$table} WHERE id=?");
        $statement->execute([$id]);
        if ($statement->fetchColumn() == false) {
            $this->addError($key, 'exists', [$table]);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $table
     * @param PDO $pdo
     * @param int|null $exclude
     * @return $this
     */
    public function unique(string $key, string $table, PDO $pdo, int $exclude = null): self
    {
        $value = $this->getValue($key);
        $query = "SELECT id FROM $table WHERE $key=?";
        $params = [$value];
        if ($exclude !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude;
        }
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        if ($statement->fetchColumn() !== false) {
            $this->addError($key, 'unique', [$value]);
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @return self
     */
    public function uploaded(string $key): self
    {
        $file = $this->getValue($key);
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            $this->addError($key, 'uploaded');
        }
        return $this;
    }

    /**
     * @param string $key
     * @param array $extensions
     * @return $this
     */
    public function extension(string $key, array $extensions): self
    {
        /** @var UploadedFileInterface $file */
        $file = $this->getValue($key);
        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $type = $file->getClientMediaType();
            $extension = mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType = self::MIME_TYPES[$extension] ?? null;
            if (!in_array($extension, $extensions) || $expectedType !== $type) {
                $this->addError($key, 'filetype', [join(',', $extensions)]);
            }
        }
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Undocumented function
     *
     * @return ValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param string $rule
     * @param array $attributes
     * @return void
     */
    private function addError(string $key, string $rule, array $attributes = []): void
    {
        $this->errors[$key] = new ValidationError($key, $rule, $attributes);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }
}
