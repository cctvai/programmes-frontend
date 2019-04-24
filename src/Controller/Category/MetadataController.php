<?php

declare(strict_types = 1);

namespace App\Controller\Category;

use App\Controller\BaseController;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Service\CategoriesService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;

class MetadataController extends BaseController
{
    public function __invoke(
        string $categoryType,
        string $categoryHierarchy,
        CategoriesService $categoriesService,
        ProgrammesService $programmesService
    ) {
        $this->setAtiContentLabels('dank', 'memes');

        switch ($categoryType) {
            case 'genres':
                $category = $categoriesService->findGenreByUrlKeyAncestry(
                    array_reverse(explode('/', $categoryHierarchy)),
                    CacheInterface::MEDIUM
                );
                if ($category === null) {
                    throw $this->createNotFoundException('Genre does not exist.');
                }
                $children = $categoriesService->findPopulatedChildGenres($category);
                break;
            case 'formats':
                $category = $categoriesService->findFormatByUrlKeyAncestry(
                    $categoryHierarchy,
                    CacheInterface::MEDIUM
                );
                if ($category === null) {
                    throw $this->createNotFoundException('Format does not exist.');
                }
                $children = [];
                break;
            default:
                throw $this->createNotFoundException('Category does not exist.');
        }

        $this->overridenDescription = 'Find BBC programmes categorised as "' . $category->getHierarchicalTitle() . '".';

        return $this->renderWithChrome('category/metadata.html.twig', [
            'categoryType' => $categoryType,
            'category' => $category,
            'availableCount' => $programmesService->countAvailableTleosByCategory(
                $category,
                CacheInterface::MEDIUM
            ),
            'children' => $children,
        ]);
    }
}
