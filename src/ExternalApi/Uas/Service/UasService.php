<?php
declare(strict_types=1);

namespace App\ExternalApi\Uas\Service;

use Closure;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class UasService
{
    const HEADERS = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'X-API-Key' => 'rt5uf8v9aol56',
    ];

    /** @var ClientInterface */
    private $client;

    /** @var string */
    private $baseUrl;

    public function __construct(ClientInterface $client, string $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
    }

    public function createActivity(
        string $idv5AccessToken,
        string $activityType,
        string $resourceDomain,
        string $resourceType,
        string $resourceId
    ): PromiseInterface {
        return $this->client->requestAsync(
            'POST',
            $this->baseUrl . '/' . urlencode($activityType),
            [
                'cookies' => $this->bakeCookies($idv5AccessToken),
                'headers' => self::HEADERS,
                'body' => json_encode([
                    'resourceDomain' => $resourceDomain,
                    'resourceType' => $resourceType,
                    'resourceId' => $resourceId,
                ]),
            ]
        )->then(Closure::fromCallable([$this, 'handle202']));
    }

    public function deleteActivity(
        string $idv5AccessToken,
        string $activityType,
        string $resourceDomain,
        string $resourceType,
        string $resourceId
    ): PromiseInterface {
        return $this->client->requestAsync(
            'DELETE',
            $this->baseUrl . '/' . urlencode($activityType) . '/urn:bbc:' . urlencode($resourceDomain) . ':' . urlencode($resourceType) . ':' . urlencode($resourceId),
            [
                'cookies' => $this->bakeCookies($idv5AccessToken),
                'headers' => self::HEADERS,
            ]
        )->then(Closure::fromCallable([$this, 'handle202']));
    }

    public function getActivity(
        string $idv5AccessToken,
        string $activityType,
        string $resourceDomain,
        string $resourceType,
        string $resourceId
    ): PromiseInterface {
        return $this->client->requestAsync(
            'GET',
            $this->baseUrl . '/' . urlencode($activityType) . '/urn:bbc:' . urlencode($resourceDomain) . ':' . urlencode($resourceType) . ':' . urlencode($resourceId),
            [
                'cookies' => $this->bakeCookies($idv5AccessToken),
                'headers' => self::HEADERS,
            ]
        )->then(Closure::fromCallable([$this, 'handle200']));
    }

    private function bakeCookies(string $idv5AccessToken)
    {
        return CookieJar::fromArray([
            'ckns_atkn' => $idv5AccessToken,
        ], 'bbc.co.uk');
    }

    private function handle200(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 200;
    }

    private function handle202(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 202;
    }
}
