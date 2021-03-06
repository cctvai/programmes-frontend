<?php
declare(strict_types = 1);

namespace App\ExternalApi\Client\Factory;

use App\ExternalApi\Client\HttpApiMultiClient;
use BBC\ProgrammesCachingLibrary\Cache;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesCachingLibrary\CacheWithResilience;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class HttpApiClientFactory
{
    private const DEFAULT_GUZZLE_OPTIONS = ['timeout' => 6];
    /** @var ClientInterface */
    private $client;

    /** @var Cache */
    private $cache;

    /** @var CacheWithResilience */
    private $cacheWithResilience;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ClientInterface $client,
        Cache $cache,
        CacheWithResilience $cacheWithResilience,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->cache = $cache;
        $this->cacheWithResilience = $cacheWithResilience;
        $this->logger = $logger;
    }

    public function getHttpApiMultiClient(
        string $cacheKey,
        array $requestUrls,
        callable $parseResponse,
        array $parseResponseArguments = [],
        $resultOnError = [],
        $standardTTL = CacheInterface::NORMAL,
        $notFoundTTL = CacheInterface::SHORT,
        array $guzzleOptions = [],
        bool $bubbleFailure = false
    ) {
        $guzzleOptions = array_merge(self::DEFAULT_GUZZLE_OPTIONS, $guzzleOptions);
        return new HttpApiMultiClient(
            $this->client,
            $bubbleFailure ? $this->cacheWithResilience : $this->cache,
            $this->logger,
            $cacheKey,
            $requestUrls,
            $parseResponse,
            $parseResponseArguments,
            $resultOnError,
            $standardTTL,
            $notFoundTTL,
            $guzzleOptions,
            $bubbleFailure
        );
    }

    public function keyHelper(string $className, string $functionName, ...$uniqueValues): string
    {
        return $this->cache->keyHelper($className, $functionName, ...$uniqueValues);
    }
}
