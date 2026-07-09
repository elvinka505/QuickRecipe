<?php

namespace App\Core;

use Dotenv\Dotenv;
use App\Exceptions\ConfigException;
use App\Exceptions\ConfigFileNotFoundException;
use App\Exceptions\ConfigValidationException;

class Config
{
    private array $data = [];

    private array $requiredKeys = [
        'APP_NAME',
        'APP_ENV',
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
    ];

    public function __construct(string $basePath, private readonly Logger $logger)
    {
        $this->load($basePath);
        $this->validate();
        $this->logger->info('Конфигурация успешно загружена');
    }

    private function load(string $basePath): void
    {
        $envPath = rtrim($basePath, '/') . '/.env';

        if (!file_exists($envPath)) {
            $e = new ConfigFileNotFoundException($envPath);
            $this->logger->logException($e);
            throw $e;
        }

        try {
            $dotenv = Dotenv::createImmutable($basePath);
            $dotenv->load();

            foreach ($_ENV as $key => $value) {
                $this->data[$key] = $value;
            }
        } catch (\Dotenv\Exception\InvalidPathException $e) {
            $ex = new ConfigFileNotFoundException($envPath, 0, $e);
            $this->logger->logException($ex);
            throw $ex;
        } catch (\Dotenv\Exception\InvalidFileException $e) {
            $ex = new ConfigException('Ошибка парсинга .env: ' . $e->getMessage(), 0, $e);
            $this->logger->logException($ex);
            throw $ex;
        } catch (\Exception $e) {
            $ex = new ConfigException('Непредвиденная ошибка: ' . $e->getMessage(), 0, $e);
            $this->logger->logException($ex);
            throw $ex;
        }
    }

    private function validate(): void
    {
        $missing = [];
        foreach ($this->requiredKeys as $key) {
            if (!isset($this->data[$key]) || $this->data[$key] === '') {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            $e = new ConfigValidationException($missing);
            $this->logger->logException($e);
            throw $e;
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function isDebug(): bool
    {
        return filter_var($this->get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    public function getEnvironment(): string
    {
        return $this->get('APP_ENV', 'prod');
    }
}
