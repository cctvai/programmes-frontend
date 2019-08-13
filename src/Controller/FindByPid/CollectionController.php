<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\Controller\Helpers\Breadcrumbs;
use App\DsShared\Utilities\Paginator\PaginatorPresenter;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;

class CollectionController extends BaseController
{
    const ITEMS_PER_PAGE = 24;

    public function __invoke(
        Collection $collection,
        PromotionsService $promotionsService,
        CoreEntitiesService $coreEntitiesService,
        RelatedLinksService $relatedLinksService,
        Breadcrumbs $breadcrumbs
    ) {
        $this->setAtiContentLabels('list-collection', 'collection');
        $this->setAtiContentId((string) $collection->getPid());
        $this->setContextAndPreloadBranding($collection);

        $page = $this->getPage();

        if ($page === 1) {
            $promotions = $promotionsService->findActiveNonSuperPromotionsByContext($collection, 4, 1, CacheInterface::MEDIUM);
        } else {
            $promotions = [];
        }

        $coreEntities = $coreEntitiesService->findByGroup($collection, self::ITEMS_PER_PAGE, $page);

        if (count($coreEntities) < 1 && (count($promotions) < 1 || $page > 1)) {
            throw $this->createNotFoundException('page number not in range');
        }

        $count = $coreEntitiesService->countByGroup($collection);
        if ($count > self::ITEMS_PER_PAGE) {
            $paginatorPresenter = new PaginatorPresenter($page, self::ITEMS_PER_PAGE, $count);
        } else {
            $paginatorPresenter = null;
        }

        $this->breadcrumbs = $breadcrumbs
            ->forNetwork($collection->getNetwork())
            ->forEntityAncestry($collection)
            ->toArray();

        return $this->renderWithChrome('find_by_pid/collection.html.twig', [
            'collection' => $collection,
            'promotions' => $promotions,
            'coreEntities' => $coreEntities,
            'relatedLinks' => $relatedLinksService->findByRelatedToProgramme($collection, ['related_site', 'miscellaneous']),
            'paginatorPresenter' => $paginatorPresenter,
        ]);
    }
}
