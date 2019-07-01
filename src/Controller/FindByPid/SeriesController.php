<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\Helpers\StructuredDataHelper;
use App\DsAmen\Factory\PresenterFactory;
use App\DsShared\Factory\HelperFactory;
use App\ExternalApi\Ada\Service\AdaClassService;
use App\ExternalApi\Electron\Service\ElectronService;
use App\ExternalApi\FavouritesButton\Service\FavouritesButtonService;
use App\ExternalApi\Morph\Service\LxPromoService;
use App\ExternalApi\RecEng\Service\RecEngService;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ImagesService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\PromotionsService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;
use Symfony\Component\HttpFoundation\Request;

class SeriesController extends BaseProgrammeContainerController
{
    public function __invoke(
        PresenterFactory $presenterFactory,
        Request $request,
        ProgrammeContainer $programme,
        ProgrammesService $programmesService,
        PromotionsService $promotionsService,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        ProgrammesAggregationService $aggregationService,
        ImagesService $imagesService,
        RecEngService $recEngService,
        ElectronService $electronService,
        AdaClassService $adaClassService,
        HelperFactory $helperFactory,
        RelatedLinksService $relatedLinksService,
        FavouritesButtonService $favouritesButtonService,
        LxPromoService $lxPromoService,
        StructuredDataHelper $structuredDataHelper
    ) {
        $this->setAtiContentLabels('series', 'series');
        return parent::__invoke(
            $presenterFactory,
            $request,
            $programme,
            $programmesService,
            $promotionsService,
            $collapsedBroadcastsService,
            $aggregationService,
            $imagesService,
            $recEngService,
            $electronService,
            $adaClassService,
            $helperFactory,
            $relatedLinksService,
            $favouritesButtonService,
            $lxPromoService,
            $structuredDataHelper
        );
    }

    protected function hasPriorityPromotion(
        ProgrammeContainer $programme,
        array $promotions,
        bool $shouldDisplayMiniMap
    ): bool {
        return false;
    }

    protected function shouldDisplayLxPromo(ProgrammeContainer $programme): bool
    {
        return false;
    }

    protected function shouldDisplayMiniMap(Request $request, ProgrammeContainer $programme, bool $isVotePriority, bool $hasLxPromo): bool
    {
        return false;
    }

    protected function shouldDisplayPriorityText(): bool
    {
        return false;
    }

    protected function shouldDisplayVote(): bool
    {
        return false;
    }
}
