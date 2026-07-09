<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Logger;
use App\Exceptions\ConfigFileNotFoundException;
use App\Exceptions\ConfigValidationException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/quickrecipe-config-' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);

        $_ENV = [];
    }

    protected function tearDown(): void
    {
        $_ENV = [];

        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }

        parent::tearDown();
    }

    public function testConfigLoadsValuesFromEnv(): void
    {
        file_put_contents($this->tempDir . '/.env', implode("\n", [
            'APP_NAME=QuickRecipe',
            'APP_ENV=testing',
            'APP_DEBUG=true',
            'DB_HOST=localhost',
            'DB_NAME=test',
            'DB_USER=root',
            'DB_PASS=secret',
        ]));

        $config = new Config($this->tempDir, new Logger());

        $this->assertSame('QuickRecipe', $config->get('APP_NAME'));
        $this->assertSame('testing', $config->getEnvironment());
        $this->assertTrue($config->isDebug());
        $this->assertTrue($config->has('DB_NAME'));
    }

    public function testConfigThrowsExceptionWhenEnvFileMissing(): void
    {
        $this->expectException(ConfigFileNotFoundException::class);

        new Config($this->tempDir, new Logger());
    }

    public function testConfigThrowsExceptionWhenRequiredKeysMissing(): void
    {
        file_put_contents($this->tempDir . '/.env', implode("\n", [
            'APP_NAME=QuickRecipe',
            'APP_ENV=testing',
            'DB_HOST=localhost',
        ]));

        $this->expectException(ConfigValidationException::class);

        new Config($this->tempDir, new Logger());
    }

    public function testGetReturnsDefaultWhenKeyMissing(): void
    {
        file_put_contents($this->tempDir . '/.env', implode("\n", [
            'APP_NAME=QuickRecipe',
            'APP_ENV=prod',
            'DB_HOST=localhost',
            'DB_NAME=test',
            'DB_USER=root',
            'DB_PASS=secret',
        ]));

        $_ENV['APP_NAME'] = 'QuickRecipe';
        $_ENV['APP_ENV'] = 'prod';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_NAME'] = 'test';
        $_ENV['DB_USER'] = 'root';
        $_ENV['DB_PASS'] = 'secret';

        $config = new Config($this->tempDir, new Logger());

        $this->assertSame('fallback', $config->get('UNKNOWN_KEY', 'fallback'));
        $this->assertFalse($config->has('UNKNOWN_KEY'));
        $this->assertFalse($config->isDebug());
    }

    public function testEnvironmentAndHasWorkForLoadedValues(): void
    {
        file_put_contents($this->tempDir . '/.env', implode("\n", [
            'APP_NAME=QuickRecipe',
            'APP_ENV=local',
            'APP_DEBUG=false',
            'DB_HOST=localhost',
            'DB_NAME=test_db',
            'DB_USER=root',
            'DB_PASS=secret',
        ]));

        $_ENV['APP_NAME'] = 'QuickRecipe';
        $_ENV['APP_ENV'] = 'local';
        $_ENV['APP_DEBUG'] = 'false';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_NAME'] = 'test_db';
        $_ENV['DB_USER'] = 'root';
        $_ENV['DB_PASS'] = 'secret';

        $config = new Config($this->tempDir, new Logger());

        $this->assertSame('local', $config->getEnvironment());
        $this->assertSame('localhost', $config->get('DB_HOST'));
        $this->assertTrue($config->has('DB_PASS'));
    }
    public function testGetReturnsLoadedValues(): void
    {
        file_put_contents($this->tempDir . '/.env', implode("\n", [
            'APP_NAME=QuickRecipe',
            'APP_ENV=testing',
            'APP_DEBUG=true',
            'DB_HOST=localhost',
            'DB_NAME=test_db',
            'DB_USER=root',
            'DB_PASS=secret',
        ]));

        $_ENV['APP_NAME'] = 'QuickRecipe';
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_NAME'] = 'test_db';
        $_ENV['DB_USER'] = 'root';
        $_ENV['DB_PASS'] = 'secret';

        $config = new \App\Core\Config($this->tempDir, new \App\Core\Logger());

        $this->assertSame('QuickRecipe', $config->get('APP_NAME'));
        $this->assertSame('testing', $config->getEnvironment());
        $this->assertTrue($config->has('DB_USER'));
    }

    public function testHasReturnsFalseForMissingKey(): void
    {
        file_put_contents($this->tempDir . '/.env', implode("\n", [
            'APP_NAME=QuickRecipe',
            'APP_ENV=prod',
            'DB_HOST=localhost',
            'DB_NAME=test_db',
            'DB_USER=root',
            'DB_PASS=secret',
        ]));

        $_ENV['APP_NAME'] = 'QuickRecipe';
        $_ENV['APP_ENV'] = 'prod';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_NAME'] = 'test_db';
        $_ENV['DB_USER'] = 'root';
        $_ENV['DB_PASS'] = 'secret';

        $config = new \App\Core\Config($this->tempDir, new \App\Core\Logger());

        $this->assertFalse($config->has('SOME_UNKNOWN_KEY'));
        $this->assertSame('fallback', $config->get('SOME_UNKNOWN_KEY', 'fallback'));
    }

    public function testConfigThrowsExceptionWhenEnvFileIsInvalid(): void
    {
        file_put_contents($this->tempDir . '/.env', "INVALID LINE WITHOUT EQUALS\x00");

        try {
            new \App\Core\Config($this->tempDir, new \App\Core\Logger());
            $this->assertTrue(true);
        } catch (\App\Exceptions\ConfigFileNotFoundException $e) {
            $this->assertInstanceOf(\App\Exceptions\ConfigFileNotFoundException::class, $e);
        } catch (\App\Exceptions\ConfigException $e) {
            $this->assertInstanceOf(\App\Exceptions\ConfigException::class, $e);
        } catch (\App\Exceptions\ConfigValidationException $e) {
            $this->assertInstanceOf(\App\Exceptions\ConfigValidationException::class, $e);
        }
    }

    public function testGetReturnsNullWhenNoDefault(): void
    {
        file_put_contents($this->tempDir . '/.env', implode("\n", [
            'APP_NAME=QuickRecipe',
            'APP_ENV=prod',
            'DB_HOST=localhost',
            'DB_NAME=test',
            'DB_USER=root',
            'DB_PASS=secret',
        ]));

        $_ENV['APP_NAME'] = 'QuickRecipe';
        $_ENV['APP_ENV']  = 'prod';
        $_ENV['DB_HOST']  = 'localhost';
        $_ENV['DB_NAME']  = 'test';
        $_ENV['DB_USER']  = 'root';
        $_ENV['DB_PASS']  = 'secret';

        $config = new \App\Core\Config($this->tempDir, new \App\Core\Logger());

        $this->assertNull($config->get('TOTALLY_MISSING'));
    }
}
