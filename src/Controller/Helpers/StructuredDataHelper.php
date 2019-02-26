<?php
declare(strict_types = 1);

namespace App\Controller\Helpers;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use App\ExternalApi\Recipes\Domain\Recipe;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Service;

/**
 * Method names are BBC domain language
 * Methods call out to Schema.org domain language methods from SchemaHelper
 */
class StructuredDataHelper
{
    /** @var SchemaHelper */
    private $schemaHelper;

    public function __construct(SchemaHelper $schemaHelper)
    {
        $this->schemaHelper = $schemaHelper;
    }

    public function getSchemaForBroadcast(Broadcast $broadcast): array
    {
        $broadcastEvent = $this->schemaHelper->getSchemaForBroadcastEvent($broadcast);
        $broadcastEvent['publishedOn'] = $this->getSchemaForService($broadcast->getService());

        return $broadcastEvent;
    }

    public function getSchemaForCollapsedBroadcast(CollapsedBroadcast $collapsedBroadcast): array
    {
        $broadcastEvent = $this->schemaHelper->getSchemaForBroadcastEvent($collapsedBroadcast);

        $broadcastEvent['publishedOn'] = [];
        foreach ($collapsedBroadcast->getServices() as $service) {
            $broadcastEvent['publishedOn'][] = $this->getSchemaForService($service);
        }

        return $broadcastEvent;
    }

    public function getSchemaForOnDemand(Episode $episode): array
    {
        return $this->schemaHelper->getSchemaForOnDemandEvent($episode);
    }

    public function prepare($schemaToPrepare, $isArrayOfContexts = false): array
    {
        return $this->schemaHelper->prepare($schemaToPrepare, $isArrayOfContexts);
    }

    public function getSchemaForEpisode(ProgrammeItem $programmeItem, bool $includeParent): array
    {
        $episode = $this->schemaHelper->getSchemaForEpisode($programmeItem);
        $parent = $programmeItem->getParent();
        if ($parent && $includeParent) {
            if ($parent->isTlec()) {
                $episode['partOfSeries'] = $this->schemaHelper->getSchemaForSeries($parent);
            } else {
                $episode['partOfSeries'] = $this->schemaHelper->getSchemaForSeries($parent->getTleo());
                $episode['partOfSeason'] = $this->schemaHelper->getSchemaForSeason($parent);
            }
        }
        return $episode;
    }

    public function getSchemaForCoreEntity(CoreEntity $programme): array
    {
        if ($programme instanceof Episode) {
            return $this->schemaHelper->getSchemaForEpisode($programme);
        }
        if ($programme instanceof Clip) {
            return $this->buildSchemaForClip($programme);
        }
        if ($programme instanceof Series) {
            return $this->schemaHelper->getSchemaForSeries($programme);
        }
        if ($programme instanceof ProgrammeContainer) {
            return $this->getSchemaForProgrammeContainer($programme);
        }
        return [];
    }

    public function getSchemaForProgrammeContainer(ProgrammeContainer $programmeContainer): array
    {
        if ($programmeContainer->isTlec()) {
            return $this->schemaHelper->getSchemaForSeries($programmeContainer);
        }

        /** @var Series $programmeContainer */
        return $this->schemaHelper->getSchemaForSeason($programmeContainer);
    }

    public function getSchemaForCollectionContainer(ProgrammeContainer $programmeContainer): array
    {
        if ($programmeContainer->isTlec()) {
            return $this->schemaHelper->getSchemaForCollection($programmeContainer);
        }

        /** @var Series $programmeContainer */
        return $this->schemaHelper->getSchemaForSeason($programmeContainer);
    }

    public function buildSchemaForClip(Clip $clip) :array
    {
        return $this->schemaHelper->buildSchemaForClip($clip);
    }

    public function getSchemaForActorContribution(Contribution $contribution): array
    {
        return $this->schemaHelper->buildSchemaForActor($contribution);
    }

    public function getSchemaForNonActorContribution(Contribution $contribution): array
    {
        return $this->schemaHelper->buildSchemaForContributor($contribution);
    }


    public function getSchemaForImage(Image $image, Programme $programme, Gallery $gallery, bool $showParent = true)
    {
        $schema = $this->schemaHelper->buildSchemaForImage($image, $programme);
        if ($showParent) {
            $schema['isPartOf'] = $this->getSchemaForGallery($gallery, $programme);
        }

        return $schema;
    }

    public function getSchemaForGallery(Gallery $gallery, Programme $programme)
    {
        return $this->schemaHelper->buildSchemaForGallery($gallery, $programme);
    }

    public function getSchemaForProgrammeContainerAndParents(Programme $programmeContainer): array
    {
        $schemaContext = $this->getSchemaForProgrammeContainer($programmeContainer);
        if ($programmeContainer->isTlec()) {
            return $schemaContext;
        }
        $ancestry = \array_slice($programmeContainer->getAncestry(), 1); // First item is the programme itself, we only want the parents
        $tleo = array_pop($ancestry); // last item is the TLEO (pop removes this from the ancestry array too)
        foreach ($ancestry as $ancestor) {
            $schemaContext['partOfSeason'] = $this->getSchemaForProgrammeContainer($ancestor);
        }
        $schemaContext['partOfSeries'] = $this->getSchemaForProgrammeContainer($tleo);

        return $schemaContext;
    }

    public function getSchemaForCollection(Collection $collection, ?Programme $programme): array
    {
        $schemaContext = $this->schemaHelper->getSchemaForCollection($collection);
        if (!$programme) {
            return $schemaContext;
        }
        $schemaContext['isPartOf'] = $this->getSchemaForProgrammeContainerAndParents($programme);

        return $schemaContext;
    }

    public function getSchemaForRecipe(Recipe $recipe): array
    {
        $schema = $this->schemaHelper->getSchemaForRecipe($recipe);

        return $schema;
    }

    private function getSchemaForService(Service $service): array
    {
        $serviceContext = $this->schemaHelper->getSchemaForService($service);

        $network = $service->getNetwork();
        if ($network !== null && $network->getName() !== $service->getName()) {
            $networkContext = $this->schemaHelper->getSchemaForService($network);
            $networkContext['logo'] =  $network->getImage()->getUrl(480);
            $serviceContext['parentService'] = $networkContext;
        }

        return $serviceContext;
    }
}
