<?php

declare(strict_types=1);

namespace Lelastico\Tracers;

use Lelastico\Contracts\TracerContract;
use Lelastico\Services\SentryService;
use Sentry\Tracing\Span;

class SentryTracer implements TracerContract
{
    protected ?Span $span = null;

    public function __construct(
        private SentryService $sentryService
    ) {
    }

    public function start(string $measurementName): bool
    {
        if ($this->sentryService->isEnabled() === false) {
            return false;
        }

        $parentSpan = $this->sentryService->getCurrentTracingSpan();

        if ($parentSpan instanceof Span === false) {
            return false;
        }

        $spanContext = $this->sentryService->createSpan();
        $spanContext->setOp('elasticsearch.query');
        $spanContext->setDescription($measurementName);

        $this->span = $parentSpan->startChild($spanContext);

        return true;
    }

    public function finish(int $took): void
    {
        if ($this->span === null) {
            return;
        }

        $this->span->finish();
    }
}
