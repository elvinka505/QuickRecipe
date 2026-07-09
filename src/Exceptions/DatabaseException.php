<?php

declare(strict_types=1);

namespace App\Exceptions;

//ошибки бд
class DatabaseException extends AppException
{
    #[\Override]
    public function getUserMessage(): string
    {
        return 'Ошибка при обращении к базе данных. Попробуйте позже.';
    }
}
