<?php

declare(strict_types=1);

namespace App\Exceptions;

class AppException extends \Exception
{
    public function getUserMessage(): string
    {
        return 'Произошла внутренняя ошибка. Попробуйте позже.';
    }
}
