<?php

namespace App\Metrics;

use App\ExternalApi\ApiType\Mapper\UriToApiTypeMapper;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class MetricsMiddleware
{
    /** @var MetricsManager */
    private $metricsManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var UriToApiTypeMapper */
    private $uriToApiTypeMapper;

    public function __construct(MetricsManager $metricsManager, LoggerInterface $logger, UriToApiTypeMapper $uriToApiTypeMapper)
    {
        $this->metricsManager = $metricsManager;
        $this->logger = $logger;
        $this->uriToApiTypeMapper = $uriToApiTypeMapper;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $this->logger->info("HTTP Request: " . $request->getUri());

            $options['on_stats'] = function (TransferStats $stats) use (&$responseTime) {
                $uri = $stats->getEffectiveUri();
                $apiName = $this->uriToApiTypeMapper->getApiNameFromUriInterface($uri);
                $responseTime = (int) round($stats->getTransferTime() * 1000);

                if ($stats->hasResponse()) {
                    $responseCode = $stats->getResponse()->getStatusCode();
                }
                else {
                    // if there's no response object, get CURL error code (0-94)
                    $responseCode = $stats->getHandlerErrorData();
                }
                if ($responseCode >= 400 && $responseCode <= 599 && $responseCode !== 404) {
                    $this->logger->error('HTTP request failed: ' . $uri . ' - got status code ' . $responseCode);
                }
                if (!$apiName) {
                    return;
                }

                $this->metricsManager->addApiMetric($apiName, $responseTime, $responseCode);
            };

            return $handler($request, $options);
        };
    }
}
