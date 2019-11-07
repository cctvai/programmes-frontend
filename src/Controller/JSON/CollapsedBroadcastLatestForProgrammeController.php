<?php

namespace App\Controller\JSON;

use App\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\JsonResponse;

class CollapsedBroadcastLatestForProgrammeController extends BaseApsController
{
    public function __invoke(
        ProgrammesService $ps,
        CollapsedBroadcastsService $cbs,
        string $pid
    ) : JsonResponse {
        $pid = new Pid($pid);

        // Only valid for Brands and Series
        $programme = $ps->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('Not Found');
        }

        $latestBroadcast = $cbs->findPastByProgramme($programme, 1);

        // Get only the first collapsed broadcast because the one we got from the service could potentially be split
        // into two or more
        $mappedBroadcasts = array_slice($this->mapManyApsObjects(new CollapsedBroadcastMapper(), $latestBroadcast), 0, 1);

        return $this->json(['broadcasts' => $mappedBroadcasts]);
    }
}
