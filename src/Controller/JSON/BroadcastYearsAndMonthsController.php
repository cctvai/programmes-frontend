<?php

namespace App\Controller\JSON;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use Symfony\Component\HttpFoundation\JsonResponse;

class BroadcastYearsAndMonthsController extends BaseApsController
{
    public function __invoke(
        ProgrammesService $ps,
        CollapsedBroadcastsService $cbs,
        Pid $pid
    ): JsonResponse {

        // Only valid for Brands and Series
        $programme = $ps->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('ProgrammeContainer Not Found');
        }

        $yearsAndMonths = $cbs->findBroadcastYearsAndMonthsByProgramme($programme);

        $years = [];
        foreach ($yearsAndMonths as $year => $months) {
            $years[] = [
                'id' => $year,
                'months' => array_map(function ($month) {
                    return ['id' => $month];
                }, $months),
            ];
        }

        return $this->json([
            'filters' => [
                'years' => $years,
                'tags' => [], // Unused, leave empty
            ],
        ]);
    }
}
