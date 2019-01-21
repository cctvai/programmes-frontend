<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Isite\FileQuery;
use App\ExternalApi\Isite\IsiteFeedResponseHandler;
use App\ExternalApi\Isite\IsiteResult;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Closure;

class ContactPageService
{
    /** @var string */
    protected $baseUrl;

    /** @var HttpApiClientFactory */
    protected $clientFactory;

    /** @var IsiteFeedResponseHandler */
    protected $responseHandler;

    public function __construct(
        string $baseUrl,
        HttpApiClientFactory $httpApiClientFactory,
        IsiteFeedResponseHandler $responseHandler
    ) {
        $this->baseUrl = $baseUrl;
        $this->clientFactory = $httpApiClientFactory;
        $this->responseHandler = $responseHandler;
    }

    public function getContactPageByCoreEntity(CoreEntity $coreEntity, $preview = false): PromiseInterface
    {
        $projectSpace = $coreEntity->getOption('project_space');
        if (empty($projectSpace) || !is_string($projectSpace)) {
            // No project space - no contact page
            return new FulfilledPromise(new IsiteResult(1, 1, 0, []));
        }
        $query = new FileQuery();
        $query->setProjectId($projectSpace)
            ->setFileId('programmes-contact-' . (string) $coreEntity->getPid());

        $cacheLifetime = CacheInterface::NORMAL;
        if ($preview) {
            $cacheLifetime = CacheInterface::NONE;
            $query
                ->setPreview(true)
                ->setAllowNonLive(true);
        }

        $cacheKey = $this->clientFactory->keyHelper(get_class($this), __FUNCTION__, $coreEntity->getPid(), $preview);

        $client = $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            [$this->baseUrl . $query->getPath()],
            Closure::fromCallable([$this, 'parseResponse']),
            [],
            new IsiteResult(1, 1, 0, []),
            $cacheLifetime,
            CacheInterface::NONE,
            [
                'connect_timeout' => 10, // Raised from 5s default to ward against PROGRAMMES-6816, re-examine after that
                'timeout' => 15,
            ],
            true
        );

        return $client->makeCachedPromise();
    }

    /**
     * @param Response[] $responses
     * @return IsiteResult
     */
    public function parseResponse(array $responses): IsiteResult
    {
        return $this->responseHandler->getIsiteResult($responses[0]);
    }
}
