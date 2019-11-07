<?php

namespace App\Controller\JSON;

use App\ApsMapper\ProgrammeChildrenProgrammeMapper;
use App\Controller\Helpers\ApsPagingHelper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProgrammeChildrenController extends BaseApsController
{
    public function __invoke(
        ProgrammesService $programmesService,
        Request $request,
        Pid $pid
    ): JsonResponse {
        [$page, $limit] = ApsPagingHelper::getPageAndLimit($request, ['limit' => 50]);

        $programme = $programmesService->findByPid($pid);

        if (is_null($programme)) {
            throw $this->createNotFoundException(sprintf('The item with PID "%s" was not found', $pid));
        }

        $totalCount = $programmesService->countEpisodeGuideChildren($programme);
        if ($totalCount === 0) {
            throw $this->createNotFoundException('No children');
        }

        $programmesResult = $programmesService->findEpisodeGuideChildren($programme, $limit, $page);

        $apsChildren = $this->mapManyApsObjects(
            new ProgrammeChildrenProgrammeMapper(),
            $programmesResult
        );

        return $this->json([
            'children' => [
                'page' => $page,
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $limit * ($page - 1),
                'programmes' => $apsChildren,
            ],
        ]);
    }
}
