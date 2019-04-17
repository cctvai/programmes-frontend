<?php

declare(strict_types = 1);

namespace App\Controller\Atoz;

use App\Controller\BaseController;
use App\Ds2013\Presenters\Utilities\Paginator\PaginatorPresenter;
use BBC\ProgrammesPagesService\Service\AtozTitlesService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShowController extends BaseController
{
    private const RESULTS_PER_PAGE = 96;

    /** @var int */
    private $currentPage;

    public function __invoke(
        string $search,
        string $slice,
        AtozTitlesService $atozTitlesService,
        Request $request,
        ProgrammesService $programmesService
    ) {

        if ($slice == 'all') {
            $this->setAtiContentLabels('list-atoz', 'atoz-all');
        } elseif ($slice == 'player') {
            $this->setAtiContentLabels('list-atoz', 'atoz-available');
        }

        $this->currentPage = $this->getPage();
        if ($this->currentPage < 1) {
            $this->pageNotInRange();
        }

        if (!preg_match('/^[a-zA-Z@]$/', $search)) {
            // Throw a 404 if not a single letter or @ as that's a search and that's invalid now
            throw new NotFoundHttpException();
        }

        switch ($search) {
            case '@':
                $selectedLetter = '0-9';
                break;
            default:
                $selectedLetter = strtolower($search);
        }

        switch ($slice) {
            case 'all':
                $count = $this->verifyPageIsInRange($atozTitlesService->countTleosByFirstLetter($search));
                if ($count > 0) {
                    $results = $atozTitlesService->findTleosByFirstLetter(
                        $search,
                        self::RESULTS_PER_PAGE,
                        $this->currentPage
                    );
                } else {
                    $results = [];
                }
                break;
            case 'player':
                $count = $this->verifyPageIsInRange($atozTitlesService->countAvailableTleosByFirstLetter($search));
                if ($count > 0) {
                    $results = $atozTitlesService->findAvailableTleosByFirstLetter(
                        $search,
                        self::RESULTS_PER_PAGE,
                        $this->currentPage
                    );
                } else {
                    $results = [];
                }
                break;
            default:
                $count = 0;
                $results = [];
        }

        switch ($slice) {
            case 'all':
                $descriptionSlice = 'all';
                break;
            case 'player':
                $descriptionSlice = 'available';
                break;
            default:
                $descriptionSlice = '';
        }

        $descriptionSearch = 'beginning with ' . strtoupper($selectedLetter);

        $this->overridenDescription = 'A list of '
                                    . $descriptionSlice
                                    . ' BBC television, radio and other programmes '
                                    . $descriptionSearch
                                    . '.';

        if ($count > self::RESULTS_PER_PAGE) {
            $paginator = new PaginatorPresenter($this->currentPage, self::RESULTS_PER_PAGE, $count);
        } else {
            $paginator = null;
        }

        return $this->renderWithChrome('atoz/show.html.twig', [
            'selectedLetter' => $selectedLetter,
            'search' => $search,
            'slice' => $slice,
            'results' => $results,
            'paginator' => $paginator,
        ]);
    }

    private function pageNotInRange(): void
    {
        throw $this->createNotFoundException('Page number is out of range.');
    }

    private function verifyPageIsInRange(int $count): int
    {
        if ($this->currentPage > ceil($count / self::RESULTS_PER_PAGE) && ($count > 0 || $this->currentPage > 1)) {
            $this->pageNotInRange();
        }

        return $count;
    }
}
