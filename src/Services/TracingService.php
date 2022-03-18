<?php

declare(strict_types=1);

namespace Lelastico\Services;

use Illuminate\Contracts\Container\Container;
use Lelastico\Configs\LelasticoConfig;
use Lelastico\Contracts\TracerContract;
use Lelastico\Tracers\SentryTracer;

/**
 * @template T
 */
class TracingService
{
    /**
     * @var class-string<TracerContract>[]
     */
    private array $tracers;

    public function __construct(
        protected Container $container,
        private LelasticoConfig $config
    ) {
        $configTracers = $this->config->getTracers();
        $this->tracers = $this->config->shouldReplaceTracers()
            ? $configTracers
            : array_merge([SentryTracer::class], $configTracers);
    }

    public function start(string $measurementName): array
    {
        if ($this->config->isTracingEnabled() === false) {
            return [];
        }

        $tracers = [];
        /** @var class-string<TracerContract> $tracerClass */
        foreach ($this->tracers as $tracerClass) {
            /** @var TracerContract $tracer */
            $tracer = $this->container->make($tracerClass);

            if ($tracer->start($measurementName) === true) {
                $tracers[] = $tracer;
            }
        }

        return $tracers;
    }

    /**
     * @param array<TracerContract> $tracers
     */
    public function finish(array $tracers, int $elasticDuration): void
    {
        foreach ($tracers as $tracer) {
            $tracer->finish($elasticDuration);
        }
    }
}
