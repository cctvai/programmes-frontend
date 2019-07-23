<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Season;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;

class SeasonController extends BaseController
{
    const LIST_LIMIT = 12;

    public function __invoke(
        Season $season,
        PromotionsService $promotionsService,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        CoreEntitiesService $coreEntitiesService,
        RelatedLinksService $relatedLinksService
    ) {
        $this->setAtiContentLabels('season', 'season');
        $this->setAtiContentId((string) $season->getPid());
        $this->setContextAndPreloadBranding($season);
        $this->setInternationalStatusAndTimezoneFromContext($season);

        $promotions = $promotionsService->findActiveNonSuperPromotionsByContext($season);
        $promoPrority = false;
        $promoImage = false;

        if ($season->getOption('brand_layout') === 'promo') {
            $promoPrority = array_shift($promotions);
            $promoImage = ($promoPrority->getPromotedEntity() instanceof CoreEntity) ? $promoPrority->getPromotedEntity()->getImage() : $promoPrority->getPromotedEntity();
        }

        return $this->renderWithChrome('find_by_pid/season.html.twig', [
            'season' => $season,
            'promoPriority' => $promoPrority,
            'promotions' => $promotions,
            'promoImage' => $promoImage,
            'comingSoons' => $collapsedBroadcastsService->findUpcomingUnderGroup($season, self::LIST_LIMIT),
            'availableNows' => $coreEntitiesService->findStreamableEpisodesUnderGroup($season, self::LIST_LIMIT),
            'relatedLinks' => $relatedLinksService->findByRelatedToProgramme($season, ['related_site', 'miscellaneous']),
        ]);
    }
}
