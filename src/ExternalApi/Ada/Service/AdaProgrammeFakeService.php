<?php
declare(strict_types = 1);

namespace App\ExternalApi\Ada\Service;

use App\Builders\AdaProgrammeItemBuilder;
use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Class AdaProgrammeFakeService
 *
 * Fake class for unit tests
 */
class AdaProgrammeFakeService extends AdaProgrammeService
{
    public function findProgrammeItemsByClass(
        AdaClass $adaClass,
        string $mediaType,
        int $page,
        ?ProgrammeContainer $programmeContainer
    ): PromiseInterface {
        return new FulfilledPromise([
            AdaProgrammeItemBuilder::any()->build(),
            AdaProgrammeItemBuilder::any()->build(),
            AdaProgrammeItemBuilder::any()->build(),
            AdaProgrammeItemBuilder::any()->build(),
            AdaProgrammeItemBuilder::any()->build(),
        ]);
    }

    public function findSuggestedByProgrammeItem(Programme $programme, int $limit = 3): PromiseInterface
    {
        return new FulfilledPromise([]);
    }
}
