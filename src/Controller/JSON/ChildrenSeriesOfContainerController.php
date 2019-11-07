<?php

namespace App\Controller\JSON;

use App\ApsMapper\ChildrenSeriesOfContainerMapper;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChildrenSeriesOfContainerController extends BaseApsController
{
    public function __invoke(
        ProgrammesService $programmesService,
        string $pid
    ): JsonResponse {
        // Only valid for Brands and Series
        $programme = $programmesService->findByPid(new Pid($pid), "ProgrammeContainer");

        if (empty($programme)) {
            throw $this->createNotFoundException('Not Found');
        }

        $programmeContainers = $programmesService
            ->findChildrenSeriesByParent($programme, $programmesService::NO_LIMIT);

        if (empty($programmeContainers)) {
            throw $this->createNotFoundException('Not Found');
        }

        $series = $this->mapManyApsObjects(new ChildrenSeriesOfContainerMapper(), $programmeContainers);
        return $this->json(['programmes' => $series]);
    }
}
