<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Service;

use App\ExternalApi\Client\Factory\HttpApiClientFactory;
use App\ExternalApi\Client\HttpApiMultiClient;
use App\ExternalApi\Exception\MultiParseException;
use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use App\ExternalApi\Recipes\Mapper\RecipeMapper;
use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;

class RecipesService
{
    /** @var RecipeMapper */
    private $mapper;

    /** @var string */
    private $baseUrl;

    /** @var HttpApiClientFactory */
    private $clientFactory;

    public function __construct(
        HttpApiClientFactory $clientFactory,
        RecipeMapper $mapper,
        string $baseUrl
    ) {
        $this->mapper = $mapper;
        $this->baseUrl = $baseUrl;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @param string $pid
     * @param int $limit
     * @param int $page
     * @return PromiseInterface (returns RecipesApiResult when unwrapped)
     */
    public function fetchRecipesByPid(string $pid, int $limit = 4, int $page = 1): PromiseInterface
    {
        $client = $this->makeHttpApiClient($pid, $limit, $page);
        return $client->makeCachedPromise();
    }

    private function makeHttpApiClient(string $pid, int $limit, int $page): HttpApiMultiClient
    {
        $cacheKey = $this->clientFactory->keyHelper(__CLASS__, __FUNCTION__, $pid, $limit, $page);

        $url = $this->baseUrl . '/by/programme/' . urlencode($pid);
        $url .= '?page=' . $page . '&pageSize=' . $limit . '&sortBy=lastModified&sortSense=desc';

        $emptyResult = new RecipesApiResult([], 0);

        return $this->clientFactory->getHttpApiMultiClient(
            $cacheKey,
            [$url],
            Closure::fromCallable([$this, 'parseResponse']),
            [$pid],
            $emptyResult
        );
    }

    /**
     * @param Response[] $responses
     * @param string $pid
     * @return RecipesApiResult
     */
    private function parseResponse(array $responses, string $pid): RecipesApiResult
    {
        $items = json_decode($responses[0]->getBody()->getContents(), true);
        if (!$items || !isset($items['byProgramme'][$pid])) {
            throw new MultiParseException(0, "Invalid Recipes API JSON");
        }
        return $this->mapItems($items['byProgramme'][$pid]);
    }

    private function mapItems(array $items): RecipesApiResult
    {
        $recipes = [];

        $total = $items['count'] ?? 0;

        foreach (($items['recipes'] ?? []) as $recipe) {
            $recipes[] = $this->mapper->mapItem($recipe);
        }

        return new RecipesApiResult($recipes, $total);
    }
}
