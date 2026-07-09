<?php

declare(strict_types=1);

namespace App\Exceptions;

//маршрут не найден = 404
class NotFoundException extends AppException
{
    public function __construct(string $resource = 'Страница', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct("{$resource} не найден(а)", $code, $previous);
    }

    #[\Override]
    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
