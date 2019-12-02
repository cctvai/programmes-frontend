<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Client;

use App\ExternalApi\Client\HttpApiClient;
use App\ExternalApi\Client\Factory\HttpApiClientFactory;
use App\ExternalApi\Client\HttpApiMultiClient;
use BBC\ProgrammesCachingLibrary\Cache;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesCachingLibrary\CacheWithResilience;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HttpApiClientFactoryTest extends TestCase
{
    private $mockCache;

    private $mockCacheWithResilience;

    /** @var HttpApiClientFactory */
    private $clientFactory;

    public function setUp()
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockCache = $this->createMock(Cache::class);
        $this->mockCacheWithResilience = $this->createMock(CacheWithResilience::class);
        $this->clientFactory = new HttpApiClientFactory($mockClient, $this->mockCache, $this->mockCacheWithResilience, $mockLogger);
    }

    public function testGetHttpApiMultiClient()
    {
        $this->assertInstanceOf(
            HttpApiMultiClient::class,
            $this->clientFactory->getHttpApiMultiClient(
                'cachekey',
                ['http://api.com', 'http://www.wibble.com'],
                function () {
                    // no-op
                }
            )
        );
    }

    public function testGetHttpApiMultiClientAllParams()
    {
        $this->assertInstanceOf(
            HttpApiMultiClient::class,
            $this->clientFactory->getHttpApiMultiClient(
                'cachekey',
                ['http://api.com', 'http://www.wibble.com'],
                function () {
                    // no-op
                },
                ['some arguments'],
                null,
                CacheInterface::NORMAL,
                CacheInterface::NONE,
                ['timeout' => true]
            )
        );
    }

    public function testGetCacheKey()
    {
        $className = __CLASS__;
        $functionName = __FUNCTION__;
        $uniqueValues = ['wibble', 'bark', 'excelsior'];
        $this->mockCache->expects($this->once())
            ->method('keyHelper')
            ->with($className, $functionName, ...$uniqueValues)
            ->willReturn('The correct bloody cache key');

        $result = $this->clientFactory->keyHelper($className, $functionName, ...$uniqueValues);
        $this->assertEquals('The correct bloody cache key', $result);
    }
}
