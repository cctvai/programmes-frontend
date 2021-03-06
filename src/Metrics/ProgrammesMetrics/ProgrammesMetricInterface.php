<?php
declare(strict_types = 1);

namespace App\Metrics\ProgrammesMetrics;

interface ProgrammesMetricInterface
{
    /**
     * @return array ['cacheKey' => cacheValue,...]
     */
    public function getCacheKeyValuePairs(): array;

    /**
     * @param array $keyValuePairs ['cacheKey' => cacheValue,...]
     * @return void
     */
    public function setValuesFromCacheKeyValuePairs(array $keyValuePairs): void;

    public function getMetricData(): array;
}
