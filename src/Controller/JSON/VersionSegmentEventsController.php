<?php

namespace App\Controller\JSON;

use App\ApsMapper\VersionSegmentEventsMapper;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionSegmentEventsController extends BaseApsController
{
    public function __invoke(
        Request $request,
        ProgrammesService $programmesService,
        VersionsService $versionsService,
        SegmentEventsService $segmentEventsService,
        Pid $pid
    ): Response {

        // Attempt to find a version
        $version = $versionsService->findByPidFull($pid);
        if ($version) {
            return $this->versionResponse($segmentEventsService, $version);
        }

        // Attempt to find a clip or episode
        $programmeItem = $programmesService->findByPid($pid, 'ProgrammeItem');
        if ($programmeItem) {
            return $this->episodeOrClipResponse($versionsService, $programmeItem);
        }

        throw $this->createNotFoundException('Episode, Clip or Version not found');
    }

    private function versionResponse(SegmentEventsService $segmentEventsService, Version $version)
    {
        if (!$version->getSegmentEventCount()) {
            throw $this->createNotFoundException('No segments');
        }

        // Segment events
        $segmentEvents = $segmentEventsService->findByVersionWithContributions(
            $version,
            $segmentEventsService::NO_LIMIT
        );

        $apsSegmentEvents = $this->mapManyApsObjects(
            new VersionSegmentEventsMapper(),
            $segmentEvents
        );

        return $this->json(['segment_events' => $apsSegmentEvents]);
    }

    private function episodeOrClipResponse(VersionsService $vs, ProgrammeItem $programmeItem)
    {
        /** @var Version $originalVersion */
        $originalVersion = $vs->findOriginalVersionForProgrammeItem($programmeItem);

        if ($originalVersion && $originalVersion->getSegmentEventCount()) {
            // Redirect to original version's segments feed
            return $this->redirectToRoute('aps.version_segment_events', ['pid' => $originalVersion->getPid()]);
        }

        // 404 if original version doesn't have segments
        throw $this->createNotFoundException('The canonical version of that episode does not have segments');
    }
}
