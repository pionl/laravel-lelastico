<?php

declare(strict_types=1);

namespace Lelastico\Services;

use Sentry\Laravel\Integration;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;

class SentryService
{
    public function isEnabled(): bool
    {
        return class_exists(Integration::class);
    }

    public function getCurrentTracingSpan(): ?Span
    {
        return Integration::currentTracingSpan();
    }

    public function createSpan(): SpanContext
    {
        return new SpanContext();
    }
}
