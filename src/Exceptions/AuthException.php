<?php

declare(strict_types=1);

namespace App\Exceptions;

//ошибка авторизации
class AuthException extends AppException
{
    #[\Override]
    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
