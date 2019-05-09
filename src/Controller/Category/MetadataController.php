<?php
declare(strict_types = 1);

namespace App\Controller\Category;

use App\Controller\BaseController;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Category;
use BBC\ProgrammesPagesService\Service\CategoriesService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;

class MetadataController extends BaseController
{
    public function __invoke(
        string $categoryType,
        Category $category,
        CategoriesService $categoriesService,
        ProgrammesService $programmesService
    ) {
        $this->setAtiContentLabels('index-category', 'category-homepage');
        $this->setAtiContentId($category->getId());

        $this->overridenDescription = 'Find BBC programmes categorised as "' . $category->getHierarchicalTitle() . '".';

        return $this->renderWithChrome('category/metadata.html.twig', [
            'categoryType' => $categoryType,
            'category' => $category,
            'availableCount' => $programmesService->countAvailableTleosByCategory(
                $category,
                CacheInterface::LONG
            ),
        ]);
    }
}
