<?php
declare(strict_types=1);

namespace App\ExternalApi\Riddle\Service;

use BBC\ProgrammesMorphLibrary\MorphClient;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Log\LoggerInterface;

class RiddleService
{
    /** @var string */
    private $riddleEnv;

    /** @var MorphClient */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        MorphClient $client,
        string $riddleEnv
    ) {
        $this->logger = $logger;
        $this->client = $client;
        $this->riddleEnv = $riddleEnv;
    }

    public function getRiddleContentPromise(string $riddleId): PromiseInterface
    {
        return $this->client->makeCachedViewPromise(
            'data',
            'bbc-morph-riddle',
            'bbc-morph-riddle',
            [
                'env' => $this->riddleEnv,
                'riddleid' => $riddleId,
            ],
            []
        );
    }
}
