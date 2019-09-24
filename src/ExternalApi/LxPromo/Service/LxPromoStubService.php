<?php
declare(strict_types=1);

namespace App\ExternalApi\LxPromo\Service;

use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class LxPromoStubService extends LxPromoService
{
    public function fetchByProgrammeContainer(ProgrammeContainer $programme): PromiseInterface
    {
        return new FulfilledPromise(null);
    }
}
