<?php
declare(strict_types = 1);
namespace App\ArgumentResolver;

use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Category;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Service\CategoriesService;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * If a controller needs a Genre/Format as an argument, resolve it here
 */
class CategoryByUrlKeyValueResolver implements ArgumentValueResolverInterface
{
    private const SUPPORTED_CLASSES = [
        // CoreEntity & child classes
        Genre::class,
        Format::class,
        Category::class,
    ];

    /** @var CategoriesService */
    private $categoriesService;

    public function __construct(CategoriesService $categoriesService)
    {
        $this->categoriesService = $categoriesService;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return (
            $request->attributes->has('categoryHierarchy')
            && $request->attributes->has('categoryType')
            && \in_array($argument->getType(), self::SUPPORTED_CLASSES)
            && !$argument->isVariadic()
        );
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $type = $argument->getType();
        $category = null;
        $categoryType = $request->attributes->get('categoryType');
        $categoryHierarchy = $request->attributes->get('categoryHierarchy');

        if (is_a($type, Category::class, true)) {
            switch ($categoryType) {
                case 'genres':
                    $category = $this->categoriesService->findGenreByUrlKeyAncestryWithDescendants(
                        $this->categoriesArrayFromUrlHierarchy($categoryHierarchy),
                        CacheInterface::MEDIUM
                    );
                    break;
                case 'formats':
                    $category = $this->categoriesService->findFormatByUrlKeyAncestry(
                        $categoryHierarchy,
                        CacheInterface::MEDIUM
                    );
                    break;
                default:
                    throw new NotFoundHttpException('Category does not exist.');
            }
        }

        if (!$category) {
            throw new NotFoundHttpException(sprintf(
                'The category "%s" with URL keys "%s" was not found',
                $categoryType,
                $categoryHierarchy
            ));
        }
        yield $category;
    }

    private function categoriesArrayFromUrlHierarchy(string $categoryHierarchy): array
    {
        return array_reverse(explode('/', $categoryHierarchy, 3));
    }
}
