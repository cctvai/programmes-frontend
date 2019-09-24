<?php
declare(strict_types=1);

namespace App\ExternalApi\Riddle\Service;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

class RiddleStubService extends RiddleService
{
    public function getRiddleContentPromise(string $riddleId): PromiseInterface
    {
        return new Promise(null);
    }
}
