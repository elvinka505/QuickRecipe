<?php

declare(strict_types=1);

namespace App\Exceptions;

//.env не найден
class ConfigFileNotFoundException extends ConfigException
{
    public function __construct(string $path, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Файл конфигурации не найден: {$path}", $code, $previous);
    }

    #[\Override]
    public function getUserMessage(): string
    {
        return 'Не удалось загрузить конфигурацию приложения. Обратитесь к администратору.';
    }
}
