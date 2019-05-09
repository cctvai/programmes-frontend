<?php

declare(strict_types = 1);

namespace App\Controller\Category;

use App\Controller\BaseController;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Service\CategoriesService;

class IndexController extends BaseController
{
    public function __invoke(
        string $categoryType,
        CategoriesService $categoriesService
    ) {
        $this->setAtiContentLabels('index-category', 'list-categories');

        switch ($categoryType) {
            case 'genres':
                $categories = $categoriesService->findGenres(CacheInterface::MEDIUM);
                $descriptionNoun = 'genre';
                break;
            case 'formats':
                $categories = $categoriesService->findFormats(CacheInterface::MEDIUM);
                $descriptionNoun = 'format';
                break;
            default:
                throw $this->createNotFoundException();
        }

        $this->overridenDescription = 'Find BBC programmes filtered by ' . $descriptionNoun . '.';

        return $this->renderWithChrome('category/index.html.twig', [
            'categoryType' => $categoryType,
            'categories' => $categories,
        ]);
    }
}
