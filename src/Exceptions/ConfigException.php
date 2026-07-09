<?php

declare(strict_types=1);

namespace App\Exceptions;

//общие ошибки конфига
class ConfigException extends AppException
{
    #[\Override]
    public function getUserMessage(): string
    {
        return 'Ошибка конфигурации: ' . $this->getMessage();
    }
}
