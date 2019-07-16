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
        PromotionsService $promotionsService,
        CoreEntitiesService $coreEntitiesService,
        RelatedLinksService $relatedLinksService
    ) {
        $this->setAtiContentLabels('funny', 'reference');
        $this->setContextAndPreloadBranding($collection);

        $page = $this->getPage();

        if ($page === 1) {
            $promotions = $promotionsService->findActiveNonSuperPromotionsByContext($collection, 4, 1, CacheInterface::MEDIUM);
        } else {
            $promotions = [];
        }

        $coreEntities = $coreEntitiesService->findByGroup($collection, 24, $page);

        if (count($coreEntities) < 1 && (count($promotions) < 1 || $page > 1)) {
            throw $this->createNotFoundException('page number not in range');
        }

        return $this->renderWithChrome('find_by_pid/collection.html.twig', [
            'collection' => $collection,
            'promotions' => $promotions,
            'coreEntities' => $coreEntities,
            'relatedLinks' => $relatedLinksService->findByRelatedToProgramme($collection, ['related_site', 'miscellaneous']),
        ]);
    }
}
