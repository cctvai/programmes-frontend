<?php

namespace App\Controller\JSON;

use App\ApsMapper\FindByPidProgrammeMapper;
use App\ApsMapper\FindByPidVersionMapper;
use App\ApsMapper\FindByPidSegmentMapper;
use App\ApsMapper\FindByPidSegmentEventMapper;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ContributionsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use BBC\ProgrammesPagesService\Service\SegmentsService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class FindByPidController extends BaseApsController
{
    public function __invoke(
        ProgrammesService $ps,
        VersionsService $vs,
        SegmentsService $ss,
        SegmentEventsService $ses,
        RelatedLinksService $rls,
        ContributionsService $cns,
        BroadcastsService $bs,
        Request $request,
        Pid $pid
    ): JsonResponse {

        // Attempt to find a Programme
        $programme = $ps->findByPidFull($pid);
        if ($programme) {
            return $this->programmeResponse($ps, $vs, $rls, $programme);
        }

        // Attempt to find a Version
        $version = $vs->findByPidFull($pid);
        if ($version) {
            return $this->versionResponse($cns, $ses, $bs, $version);
        }

        // Attempt to find a Segment
        $segment = $ss->findByPidFull($pid);
        if ($segment) {
            return $this->segmentResponse($ses, $segment);
        }

        // Attempt to find a SegmentEvent
        $segmentEvent = $ses->findByPidFull($pid);
        if ($segmentEvent) {
            return $this->segmentEventResponse($ses, $segmentEvent);
        }

        throw $this->createNotFoundException(sprintf('The item with PID "%s" was not found', $pid));
    }

    private function programmeResponse(
        ProgrammesService $ps,
        VersionsService $vs,
        RelatedLinksService $rls,
        Programme $programme
    ): JsonResponse {
        // Related Links
        $relatedLinks = [];
        if ($programme->getRelatedLinksCount()) {
            $relatedLinks = $rls->findByRelatedToProgramme($programme);
        }

        // Peers
        $nextSibling = null;
        $previousSibling = null;

        /** @noinspection PhpUnhandledExceptionInspection */
        if ($programme->getParent()) {
            $nextSibling = $ps->findNextSiblingByProgramme($programme);
            $previousSibling = $ps->findPreviousSiblingByProgramme($programme);
        }

        // Versions
        $versions = [];
        if ($programme instanceof ProgrammeItem) {
            $versions = $vs->findByProgrammeItem($programme);
        }

        $apsProgramme = $this->mapSingleApsObject(
            new FindByPidProgrammeMapper(),
            $programme,
            $relatedLinks,
            $nextSibling,
            $previousSibling,
            $versions
        );

        return $this->json(['programme' => $apsProgramme]);
    }

    private function versionResponse(
        ContributionsService $contributionsService,
        SegmentEventsService $ses,
        BroadcastsService $broadcastsService,
        Version $version
    ): JsonResponse {
        // Contributors
        $contributions = [];

        if ($version->getContributionsCount()) {
            $contributions = $contributionsService->findByContributionToVersion($version);
        } elseif ($version->getProgrammeItem()->getContributionsCount()) {
            // If no contributions on Version, try on the Programme
            $contributions = $contributionsService->findByContributionToProgramme(
                $version->getProgrammeItem()
            );
        }

        // Segment Events with the contributions
        $segmentEvents = [];
        if ($version->getSegmentEventCount()) {
            $segmentEvents = $ses->findByVersionWithContributions($version);
        }

        // Broadcasts
        $broadcasts = $broadcastsService->findByVersion($version, 100);

        $apsVersion = $this->mapSingleApsObject(
            new FindByPidVersionMapper(),
            $version,
            $contributions,
            $segmentEvents,
            $broadcasts
        );

        return $this->json(['version' => $apsVersion]);
    }

    private function segmentResponse(
        SegmentEventsService $segmentEventsService,
        Segment $segment
    ): JsonResponse {

        $segmentEvents = $segmentEventsService->findBySegmentFull($segment, true, $segmentEventsService::NO_LIMIT);

        $apsSegment = $this->mapSingleApsObject(
            new FindByPidSegmentMapper(),
            $segment,
            $segmentEvents,
            true
        );

        return $this->json(['segment' => $apsSegment]);
    }

    private function segmentEventResponse(
        SegmentEventsService $segmentEventsService,
        SegmentEvent $segmentEvent
    ): JsonResponse {

        /** @noinspection PhpUnhandledExceptionInspection */
        $segmentEventsBySegment = $segmentEventsService->findBySegmentFull(
            $segmentEvent->getSegment(),
            true,
            $segmentEventsService::NO_LIMIT
        );

        $apsSegmentEvent = $this->mapSingleApsObject(
            new FindByPidSegmentEventMapper(),
            $segmentEvent,
            $segmentEventsBySegment
        );

        return $this->json([
            'segment_event' => $apsSegmentEvent,
        ]);
    }
}
