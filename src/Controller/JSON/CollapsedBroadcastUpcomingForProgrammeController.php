<?php

namespace App\Controller\JSON;

use App\ApsMapper\CollapsedBroadcastMapper;
use App\Controller\Helpers\ApsPagingHelper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CollapsedBroadcastUpcomingForProgrammeController extends BaseApsController
{
    public function __invoke(
        ProgrammesService $ps,
        CollapsedBroadcastsService $cbs,
        Request $request,
        Pid $pid
    ): JsonResponse {
        [$page, $limit] = ApsPagingHelper::getPageAndLimit($request);

        // Only valid for Brands and Series
        $programme = $ps->findByPid($pid, "ProgrammeContainer");
        if (!$programme) {
            throw $this->createNotFoundException('Not Found');
        }

        $totalCount = $cbs->countUpcomingByProgramme($programme);
        if (!$totalCount) {
            throw $this->createNotFoundException('No Broadcasts Found');
        }

        $offset = $limit * ($page - 1);

        // offset is 0 indexed so if you've got 10 items total and you're
        // showing 10 items per page then for page 2, offset would be 10, which
        // should throw an error (as all the items are show on page 1)
        if ($offset >= $totalCount) {
            throw $this->createNotFoundException('Invalid page number');
        }

        $latestBroadcast = $cbs->findUpcomingByProgramme($programme, $limit, $page);

        $mappedBroadcasts = $this->mapManyApsObjects(
            new CollapsedBroadcastMapper(),
            $latestBroadcast
        );

        return $this->json([
            'page' => $page,
            'total' => $totalCount,
            'offset' => $offset,
            'limit' => $limit,
            'broadcasts' => $mappedBroadcasts,
        ]);
    }
}
