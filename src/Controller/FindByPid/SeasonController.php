<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\Controller\Helpers\Breadcrumbs;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Season;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;

class SeasonController extends BaseController
{
    public function __invoke(
        Season $season,
        PromotionsService $promotionsService,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        CoreEntitiesService $coreEntitiesService,
        RelatedLinksService $relatedLinksService,
        Breadcrumbs $breadcrumbs
    ) {
        $this->setAtiContentLabels('season', 'season');
        $this->setAtiContentId((string) $season->getPid());
        $this->setContextAndPreloadBranding($season);
        $this->setInternationalStatusAndTimezoneFromContext($season);

        $promotions = $promotionsService->findActiveNonSuperPromotionsByContext($season);
        $promoPrority = false;
        $promoImage = false;

        if ($season->getOption('brand_layout') === 'promo' && $promotions) {
            $promoPrority = array_shift($promotions);
            $promoImage = ($promoPrority->getPromotedEntity() instanceof CoreEntity) ? $promoPrority->getPromotedEntity()->getImage() : $promoPrority->getPromotedEntity();
        }

        $this->breadcrumbs = $breadcrumbs
            ->forNetwork($season->getNetwork())
            ->forEntityAncestry($season)
            ->toArray();

        return $this->renderWithChrome('find_by_pid/season.html.twig', [
            'season' => $season,
            'promoPriority' => $promoPrority,
            'promotions' => $promotions,
            'promoImage' => $promoImage,
            'comingSoons' => $collapsedBroadcastsService->findUpcomingUnderGroup($season, 6),
            'availableNows' => $coreEntitiesService->findStreamableEpisodesUnderGroup($season, 12),
            'relatedLinks' => $relatedLinksService->findByRelatedToProgramme($season, ['related_site', 'miscellaneous']),
        ]);
    }
}
