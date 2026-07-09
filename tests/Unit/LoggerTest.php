<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LoggerTest extends TestCase
{
    public function testLoggerCanWriteInfoWarningAndErrorWithoutCrashing(): void
    {
        $logger = new Logger();

        $logger->info('info message');
        $logger->warning('warning message');
        $logger->error('error message');

        $this->assertTrue(true);
    }

    public function testLoggerCanLogException(): void
    {
        $logger = new Logger();

        $logger->logException(new RuntimeException('boom'));

        $this->assertTrue(true);
    }

    public function testLoggerCanWriteEmergencyAlertCriticalNoticeDebugAndGenericLog(): void
    {
        $logger = new Logger(true);

        $logger->emergency('emergency');
        $logger->alert('alert');
        $logger->critical('critical');
        $logger->notice('notice');
        $logger->debug('debug');
        $logger->log('info', 'generic log');

        $this->assertTrue(true);
    }
}
