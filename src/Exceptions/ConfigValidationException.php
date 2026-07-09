<?php

declare(strict_types=1);

namespace App\Exceptions;

class ConfigValidationException extends ConfigException
{
    public function __construct(private readonly array $missingKeys, int $code = 0, ?\Throwable $previous = null)
    {
        $message = 'Отсутствуют обязательные параметры: ' . implode(', ', $this->missingKeys);
        parent::__construct($message, $code, $previous);
    }

    public function getMissingKeys(): array
    {
        return $this->missingKeys;
    }
}
