<?php
declare(strict_types = 1);

namespace App\Controller\ProgrammeEpisodes;

use App\Controller\Helpers\Breadcrumbs;
use App\Controller\Helpers\StructuredDataHelper;
use App\Ds2013\Factory\PresenterFactory;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;

class UpcomingController extends BaseProgrammeEpisodesController
{
    public function __invoke(
        ProgrammeContainer $programme,
        ?string $debut,
        CollapsedBroadcastsService $collapsedBroadcastsService,
        PresenterFactory $presenterFactory,
        ProgrammesAggregationService $programmeAggregationService,
        StructuredDataHelper $structuredDataHelper,
        Breadcrumbs $breadcrumbs
    ) {
        $this->setContextAndPreloadBranding($programme);
        $this->setAtiContentLabels('list-tleo', 'guide-upcoming');
        $this->setInternationalStatusAndTimezoneFromContext($programme);
        $this->setAtiContentId((string) $programme->getPid(), 'pips');


        $subNavPresenter = $this->getSubNavPresenter($collapsedBroadcastsService, $programme, $presenterFactory);
        $upcomingBroadcasts = $collapsedBroadcastsService->findUpcomingByProgrammeWithFullServicesOfNetworksList($programme, 100);

        // display only debut broadcast if $debut is set
        if ($debut) {
            foreach ($upcomingBroadcasts as $key => $upcoming) {
                if ($upcoming->isRepeat()) {
                    unset($upcomingBroadcasts[$key]);
                }
            }
        }

        $schema = $this->getSchema($structuredDataHelper, $programme, $upcomingBroadcasts);

        $this->overridenDescription = 'Upcoming episodes of ' . $programme->getTitle();

        $opts = ['pid' => $programme->getPid()];
        $this->breadcrumbs = $breadcrumbs
            ->forNetwork($programme->getNetwork())
            ->forEntityAncestry($programme)
            ->forRoute('Episodes', 'programme_episodes', $opts)
            ->forRoute('Upcoming', 'programme_upcoming_broadcasts', $opts)
            ->toArray();

        return $this->renderWithChrome('programme_episodes/upcoming.html.twig', [
            'programme' => $programme,
            'upcomingBroadcasts' => $upcomingBroadcasts,
            'subNavPresenter' => $subNavPresenter,
            'debut' => $debut,
            'schema' => $schema,
        ]);
    }

    /**
     * @param StructuredDataHelper $structuredDataHelper
     * @param ProgrammeContainer $programmeContainer
     * @param CollapsedBroadcast[] $upcomingBroadcasts
     * @return array
     */
    private function getSchema(StructuredDataHelper $structuredDataHelper, ProgrammeContainer $programmeContainer, array $upcomingBroadcasts): array
    {
        $schemaContext = $structuredDataHelper->getSchemaForProgrammeContainerAndParents($programmeContainer);

        foreach ($upcomingBroadcasts as $upcomingBroadcast) {
            $episodeSchema = $structuredDataHelper->getSchemaForEpisode($upcomingBroadcast->getProgrammeItem(), false);
            $episodeSchema['publication'] = $structuredDataHelper->getSchemaForCollapsedBroadcast($upcomingBroadcast);
            $schemaContext['episode'][] = $episodeSchema;
        }

        return $structuredDataHelper->prepare($schemaContext);
    }
}
