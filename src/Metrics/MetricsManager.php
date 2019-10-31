<?php
declare(strict_types = 1);

namespace App\Metrics;

use App\ExternalApi\ApiType\ApiTypeEnum;
use App\Metrics\Backend\MetricBackendInterface;
use App\Metrics\Cache\MetricCacheInterface;
use App\Metrics\ProgrammesMetrics\ApiBreakerMetric;
use App\Metrics\ProgrammesMetrics\ApiResponseMetric;
use App\Metrics\ProgrammesMetrics\ApiTimeMetric;
use App\Metrics\ProgrammesMetrics\ProgrammesMetricInterface;
use App\Metrics\ProgrammesMetrics\RouteMetric;
use InvalidArgumentException;
use Symfony\Component\Routing\RouterInterface;

class MetricsManager
{
    /** @var ProgrammesMetricInterface[] */
    private $metrics = [];

    /** @var RouterInterface */
    private $router;

    /** @var MetricCacheInterface */
    private $cache;

    /** @var MetricBackendInterface */
    private $backend;

    /** @var array */
    private $validRouteControllers;

    /**
     * As these controllers are not listed in the routes file (they're all findByPid)
     * they cannot be discovered by looking at that file, as all other routes are.
     * Hence we list them here.
     */
    private const PROGRAMMES_FINDBYPID_ROUTE_CONTROLLERS = [
        'ClipController' => 'FindByPid/ClipController',
        'CollectionController' => 'FindByPid/CollectionController',
        'EpisodeController' => 'FindByPid/EpisodeController',
        'GalleryController' => 'FindByPid/GalleryController',
        'SeasonController' => 'FindByPid/SeasonController',
        'SegmentController' => 'FindByPid/SegmentController',
        'SeriesController' => 'FindByPid/SeriesController',
        'TlecController' => 'FindByPid/TlecController',
        'VersionController' => 'FindByPid/VersionController',
    ];

    public function __construct(RouterInterface $router, MetricCacheInterface $cache, MetricBackendInterface $backend)
    {
        $this->router = $router;
        $this->cache = $cache;
        $this->backend = $backend;
    }

    public function addRouteMetric(string $controllerClass, int $responseTimeMs) : void
    {
        $controllerName = $this->controllerName($controllerClass);
        if ($controllerName) {
            $this->metrics[] = new RouteMetric($controllerName, $responseTimeMs);
        }
    }

    public function addApiMetric(string $apiName, int $responseTimeMs, int $responseCode) : void
    {
        if (!ApiTypeEnum::isValid($apiName)) {
            throw new InvalidArgumentException("$apiName is not a valid API type");
        }
        $this->metrics[] = new ApiTimeMetric($apiName, $responseTimeMs);
        $responseType = $this->normaliseHttpResponseCode($responseCode);
        if ($responseType === 'ERROR') {
            $this->metrics[] = new ApiResponseMetric($apiName, $responseType);
        }
    }

    public function addApiCircuitBreakerOpenMetric(string $apiName): void
    {
        if (!ApiTypeEnum::isValid($apiName)) {
            throw new InvalidArgumentException("$apiName is not a valid API type");
        }
        $this->metrics[] = new ApiBreakerMetric($apiName, true);
    }

    public function send(): void
    {
        $this->cache->cacheMetrics($this->metrics);
        /** @var ProgrammesMetricInterface[] $readyToSendMetrics */
        $readyToSendMetrics = $this->cache->getAndClearReadyToSendMetrics([$this, 'getAllPossibleMetrics']);
        if ($readyToSendMetrics) {
            $this->backend->sendMetrics($readyToSendMetrics);
        }
    }

    /**
     * @return ProgrammesMetricInterface[]
     */
    public function getAllPossibleMetrics() : array
    {
        $allMetrics = [];
        foreach ($this->getAllPossibleRoutes() as $routeName) {
            $allMetrics[] = new RouteMetric($routeName, 0, 0);
        }

        foreach (ApiTypeEnum::validValues() as $api) {
            $allMetrics[] = new ApiTimeMetric($api, 0, 0);
            $allMetrics[] = new ApiResponseMetric($api, 'ERROR', 0);
            $allMetrics[] = new ApiBreakerMetric($api, false);
        }

        return $allMetrics;
    }

    /**
     * @return string[]
     */
    private function getAllPossibleRoutes() : array
    {
        if (!isset($this->validRouteControllers)) {
            $this->validRouteControllers = self::PROGRAMMES_FINDBYPID_ROUTE_CONTROLLERS;
            foreach ($this->router->getRouteCollection()->all() as $routeName => $routeInfo) {
                $controllerName = $this->controllerName($routeInfo->getDefault('_controller') ?? '');
                if ($controllerName) {
                    $this->validRouteControllers[$controllerName] = $controllerName;
                }
            }
        }
        return $this->validRouteControllers;
    }

    private function controllerName(string $controllerClass): ?string
    {
        if (strpos($controllerClass, 'App\\Controller') === 0) {
            $controllerClass = str_replace('App\\Controller\\', '', $controllerClass);
            return str_replace('\\', '/', $controllerClass);
        }
        return null;
    }

    /**
     * Decide what response codes we want to cache
     * @param int $responseCode
     * @return string
     */
    private function normaliseHttpResponseCode(int $responseCode): string
    {
        if ($responseCode == 404 || ($responseCode >= 200 && $responseCode <= 299)) {
            // 404s and 200s are OK
            return 'OK';
        }
        // Anything else is an error.
        return 'ERROR';
    }
}
