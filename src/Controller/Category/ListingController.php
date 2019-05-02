<?php
declare(strict_types = 1);

namespace App\Controller\Category;

use App\Controller\BaseController;
use App\Ds2013\Presenters\Utilities\Paginator\PaginatorPresenter;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Category;
use BBC\ProgrammesPagesService\Service\CategoriesService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingController extends BaseController
{
    private const RESULTS_PER_PAGE = 50;

    public function __invoke(
        string $slice,
        string $categoryType,
        Category $category,
        CategoriesService $categoriesService,
        ProgrammesService $programmesService
    ) {
        $this->setAtiContentLabels('communist', 'party');

        switch ($slice) {
            case 'all':
                $programmeCount = $programmesService->countAllTleosByCategory($category, CacheInterface::LONG);
                $page = $this->getAndValidatePage($programmeCount);
                $programmes = $programmesService->findAllTleosByCategory($category, self::RESULTS_PER_PAGE, $page);
                $this->overridenDescription = 'List all BBC programmes categorised as "' . $category->getHierarchicalTitle() . '".';
                break;
            case 'player':
                $programmeCount = $programmesService->countAvailableTleosByCategory($category, CacheInterface::LONG);
                $page = $this->getAndValidatePage($programmeCount);
                $programmes = $programmesService->findAvailableTleosByCategory($category, self::RESULTS_PER_PAGE, $page);
                $this->overridenDescription = 'List available BBC programmes categorised as "' . $category->getHierarchicalTitle() . '".';
                break;
            default:
                throw new NotFoundHttpException("Why are you here?");
        }
        $paginator = null;
        if ($programmeCount > self::RESULTS_PER_PAGE) {
            $paginator = new PaginatorPresenter($page, self::RESULTS_PER_PAGE, $programmeCount);
        }

        return $this->renderWithChrome('category/listing.html.twig', [
            'categoryType' => $categoryType,
            'category' => $category,
            'programmes' => $programmes,
            'paginator' => $paginator,
            'active_slice' => $slice,
        ]);
    }

    protected function getAndValidatePage($programmeCount): int
    {
        $page = $this->getPage();
        if ($page > 1 && (self::RESULTS_PER_PAGE * ($page - 1)) > $programmeCount) {
            throw $this->createNotFoundException('Page number is out of range.');
        }
        return $page;
    }
}
