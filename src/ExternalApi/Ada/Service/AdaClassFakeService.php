<?php
declare(strict_types = 1);

namespace App\ExternalApi\Ada\Service;

use App\Builders\AdaClassBuilder;
use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Class AdaClassFakeService
 *
 * Fake class for unit tests
 */
class AdaClassFakeService extends AdaClassService
{
    public function findAllClasses(int $page, ?ProgrammeContainer $programmeContainer): PromiseInterface
    {
        return new FulfilledPromise([
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
        ]);
    }

    public function findClassById(string $id): PromiseInterface
    {
        return new FulfilledPromise(AdaClassBuilder::any()->build());
    }

    public function findRelatedClassesByClass(AdaClass $adaClass): PromiseInterface
    {
        return new FulfilledPromise([
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
        ]);
    }

    public function findRelatedClassesByClassAndContainer(
        AdaClass $adaClass,
        ProgrammeContainer $programmeContainer
    ): PromiseInterface {
        return new FulfilledPromise([
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
            AdaClassBuilder::any()->build(),
        ]);
    }

    public function findRelatedClassesByContainer(
        Programme $programme,
        bool $countWithinTleo = true,
        int $limit = 5
    ): PromiseInterface {
        return new FulfilledPromise([]);
    }
}
