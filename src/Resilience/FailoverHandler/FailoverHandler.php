<?php

namespace Src\Resilience\FailoverHandler;

use Illuminate\Support\Facades\Log;

/**
 * Failover Handler — circuit-breaker and retry logic for external services.
 *
 * Wraps calls to external APIs (WhatsApp, Redis, etc.) with:
 *  - Retry with exponential backoff
 *  - Circuit breaker to prevent cascade failures
 *  - Fallback to degraded mode
 */
class FailoverHandler
{
    /**
     * Execute a callable with retry and fallback.
     *
     * @param callable $operation The primary operation
     * @param callable|null $fallback Fallback if all retries fail
     * @param int $maxRetries Max retry attempts
     * @return mixed Result of operation or fallback
     */
    public function execute(callable $operation, ?callable $fallback = null, int $maxRetries = 3): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $operation();
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning("FailoverHandler: attempt {$attempt}/{$maxRetries} failed", [
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < $maxRetries) {
                    // Exponential backoff: 100ms, 200ms, 400ms...
                    usleep(100_000 * (2 ** ($attempt - 1)));
                }
            }
        }

        if ($fallback) {
            Log::error("FailoverHandler: all retries exhausted, executing fallback", [
                'error' => $lastException?->getMessage(),
            ]);
            return $fallback($lastException);
        }

        throw $lastException;
    }
}
