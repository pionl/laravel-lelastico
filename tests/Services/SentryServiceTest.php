<?php

declare(strict_types=1);

namespace Lelastico\Tests\Services;

use Lelastico\Services\SentryService;
use PHPUnit\Framework\TestCase;
use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\State\HubInterface;
use Sentry\Tracing\Span;

class SentryServiceTest extends TestCase
{
    private HubInterface $originalHub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalHub = SentrySdk::getCurrentHub();
    }

    protected function tearDown(): void
    {
        SentrySdk::setCurrentHub($this->originalHub);

        parent::tearDown();
    }

    public function testItReturnsTheCurrentSentrySpan(): void
    {
        $span = new Span();
        $hub = new Hub();
        $hub->setSpan($span);
        SentrySdk::setCurrentHub($hub);

        self::assertSame($span, (new SentryService())->getCurrentTracingSpan());
    }
}
