<?php
declare(strict_types = 1);

namespace App\Controller\Category;

use App\Controller\BaseController;
use App\Controller\Helpers\Breadcrumbs;
use App\DsShared\Utilities\Paginator\PaginatorPresenter;
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
        ProgrammesService $programmesService,
        Breadcrumbs $breadcrumbs
    ) {
        $this->setAtiContentId($category->getId());

        switch ($slice) {
            case 'all':
                $programmeCount = $programmesService->countAllTleosByCategory($category, CacheInterface::LONG);
                $page = $this->getAndValidatePage($programmeCount);
                $programmes = $programmesService->findAllTleosByCategory($category, self::RESULTS_PER_PAGE, $page);
                $descriptionSlice = 'all';
                $this->overridenDescription = 'List of ' . $descriptionSlice . ' BBC programmes categorised as "' . $category->getHierarchicalTitle() . '".';
                $this->setAtiContentLabels('index-category', 'category-all');
                break;
            case 'player':
                $programmeCount = $programmesService->countAvailableTleosByCategory($category, CacheInterface::LONG);
                $page = $this->getAndValidatePage($programmeCount);
                $programmes = $programmesService->findAvailableTleosByCategory($category, self::RESULTS_PER_PAGE, $page);
                $descriptionSlice = 'available';
                $this->overridenDescription = 'List of ' . $descriptionSlice . ' BBC programmes categorised as "' . $category->getHierarchicalTitle() . '".';
                $this->setAtiContentLabels('index-category', 'category-available');
                break;
            default:
                throw new NotFoundHttpException("Why are you here?");
        }
        $paginator = null;
        if ($programmeCount > self::RESULTS_PER_PAGE) {
            $paginator = new PaginatorPresenter($page, self::RESULTS_PER_PAGE, $programmeCount);
        }

        $opts = ['categoryType' => $categoryType];
        $this->breadcrumbs = $breadcrumbs
            ->forRoute('Programmes', 'home')
            ->forRoute(ucfirst($categoryType), 'category_index', $opts)
            ->forCategoryAncestry($category)
            ->forRoute(
                ucfirst($descriptionSlice),
                'category_slice',
                [
                    'categoryHierarchy' => $category->getUrlKeyHierarchy(),
                    'slice' => $slice,
                ] + $opts
            )
            ->toArray();

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
