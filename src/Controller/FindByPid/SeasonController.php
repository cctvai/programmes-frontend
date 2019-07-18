<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Season;
use BBC\ProgrammesPagesService\Service\PromotionsService;

class SeasonController extends BaseController
{
    public function __invoke(Season $season, PromotionsService $promotionsService)
    {
        $this->setAtiContentLabels('foo', 'bar');
        $this->setContextAndPreloadBranding($season);

        $promotions = $promotionsService->findActiveNonSuperPromotionsByContext($season, 50);
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
        ]);
    }
}
