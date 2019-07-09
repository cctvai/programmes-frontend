<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;

class CollectionController extends BaseController
{
    public function __invoke(
        Collection $collection,
        CoreEntitiesService $coreEntitiesService,
        PromotionsService $promotionsService,
        RelatedLinksService $relatedLinksService
    ) {
        $this->setAtiContentLabels('funny', 'reference');
        $this->setContextAndPreloadBranding($collection);

        $members = $coreEntitiesService->findByGroup($collection);
        $promotions = $promotionsService->findActiveNonSuperPromotionsByContext($collection, 4, 1, CacheInterface::MEDIUM);
        $relatedLinks = $relatedLinksService->findByRelatedToProgramme($collection, ['related_site', 'miscellaneous']);

        return $this->renderWithChrome('find_by_pid/collection.html.twig');
    }
}
