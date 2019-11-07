<?php

namespace App\Controller\JSON;

use App\ApsMapper\AtozItemMapper;
use App\Controller\Helpers\ApsPagingHelper;
use BBC\ProgrammesPagesService\Domain\Entity\AtozTitle;
use BBC\ProgrammesPagesService\Service\AtozTitlesService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AtozController extends BaseApsController
{

    public function lettersListAction(
        AtozTitlesService $service,
        string $slice = 'player'
    ): JsonResponse {
        $data = $this->getLettersAndSlice($service, $slice);
        $data['tleo_titles'] = [];
        return $this->json(['atoz' => $data]);
    }

    public function byAction(
        ProgrammesService $progService,
        AtozTitlesService $azService,
        Request $request,
        string $search,
        string $slice = 'player'
    ): JsonResponse {
        [$page, $limit] = ApsPagingHelper::getPageAndLimit($request);
        [$items, $itemCount] = $this->letterSearch($azService, $search, $slice, $page, $limit);

        if (!count($items)) {
            throw $this->createNotFoundException('No results returned');
        }

        $data = $this->getLettersAndSlice($azService, $slice, $search, $page, $limit, $itemCount);
        $data['tleo_titles'] = $this->mapManyApsObjects(new AtozItemMapper(), $items);
        return $this->json(['atoz' => $data]);
    }

    private function letterSearch(
        AtozTitlesService $azService,
        string $search,
        string $slice,
        int $page,
        int $limit
    ): array {

        switch ($slice) {
            case 'player':
                $items = $azService->findAvailableTleosByFirstLetter($search, $limit, $page);
                $itemCount = $limit ? $azService->countAvailableTleosByFirstLetter($search) : count($items);
                break;
            case 'all':
                $items = $azService->findTleosByFirstLetter($search, $limit, $page);
                $itemCount = $limit ? $azService->countTleosByFirstLetter($search) : count($items);
                break;
            default:
                throw new NotFoundHttpException("Slice does not exist");
        }

        return [$items, $itemCount];
    }

    private function getLettersAndSlice(
        AtozTitlesService $service,
        string $slice,
        string $search = null,
        int $page = null,
        int $limit = null,
        int $total = null
    ) {
        $offset = ($page - 1) * $limit;
        $data = [
            'slice' => $slice,
            'by' => null,
            'search' => $search,
            'letters' => $service->findAllLetters(),
            'page' => $limit ? $page : null,
            'limit' => $limit,
            'total' => $total,
            'offset' => $offset,
        ];
        if ($search) {
            unset($data['by']);
        } else {
            unset($data['search']);
        }
        return $data;
    }
}
