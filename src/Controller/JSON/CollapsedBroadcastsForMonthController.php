<?php

namespace App\Controller\JSON;

use App\ApsMapper\CollapsedBroadcastMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CollapsedBroadcastsForMonthController extends BaseApsController
{
    public function __invoke(
        ProgrammesService $ps,
        CollapsedBroadcastsService $cbs,
        Request $request,
        string $pid,
        string $year,
        string $month
    ): JsonResponse {
        $pid = new Pid($pid);

        // Only valid for Brands and Series
        $programme = $ps->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('Not Found');
        }

        $broadcastsByMonth = $cbs->findByProgrammeAndMonth(
            $programme,
            $year,
            $month,
            $cbs::NO_LIMIT
        );

        if (empty($broadcastsByMonth)) {
            throw $this->createNotFoundException('Not Found');
        }

        $mappedBroadcasts = $this->mapManyApsObjects(new CollapsedBroadcastMapper(), $broadcastsByMonth);
        return $this->json(['broadcasts' => $mappedBroadcasts]);
    }
}
