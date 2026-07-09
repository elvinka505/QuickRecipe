<?php

declare(strict_types=1);

namespace App\Core;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Stringable;

class Logger implements LoggerInterface
{
    private readonly MonologLogger $monolog;

    public function __construct(private readonly bool $debug = false)
    {
        $logDir = dirname(__DIR__, 2) . '/runtime/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->monolog = new MonologLogger('quickrecipe');
        $this->monolog->pushHandler(
            new StreamHandler($logDir . '/app.log', Level::Debug)
        );
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->monolog->emergency($message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->monolog->alert($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->monolog->critical($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->monolog->error($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->monolog->warning($message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->monolog->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->monolog->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->monolog->debug($message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->monolog->log($level, $message, $context);
    }

    public function logException(\Throwable $e, string $level = 'error'): void
    {
        $this->monolog->$level(
            $e->getMessage() . ' | Файл: ' . $e->getFile() . ':' . $e->getLine()
        );

        if ($this->debug) {
            $this->monolog->debug('Trace: ' . $e->getTraceAsString());
        }
    }
}
