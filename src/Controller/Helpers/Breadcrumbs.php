<?php
declare(strict_types = 1);

namespace App\Controller\Helpers;

use App\ExternalApi\Isite\Domain\BaseIsiteObject;
use App\ValueObject\Breadcrumb;
use BBC\ProgrammesPagesService\Domain\Entity\Category;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Network;

class Breadcrumbs
{
    /* @var Breadcrumb[] */
    protected $breadcrumbs = [];

    /* @var BreadcrumbHelper */
    protected $helper;

    public function __construct(BreadcrumbHelper $helper, array $initial = [])
    {
        $this->helper = $helper;
        $this->breadcrumbs = $initial;
    }

    public function forEntityAncestry(CoreEntity $entity): Breadcrumbs
    {
        array_push($this->breadcrumbs, ...$this->helper->getCoreEntityAncestryBreadcrumbs($entity));
        return $this;
    }

    public function forCategoryAncestry(Category $category): Breadcrumbs
    {
        array_push($this->breadcrumbs, ...$this->helper->getCategoryAncestryBreadcrumbs($category));
        return $this;
    }

    public function forIsiteRoute(BaseIsiteObject $isiteObj, string $routeName): Breadcrumbs
    {
        return $this->forRoute(
            $isiteObj->getTitle(),
            $routeName,
            [
                'key' => $isiteObj->getKey(),
                'slug' => $isiteObj->getSlug(),
            ]
        );
    }

    public function forNetwork(?Network $network): Breadcrumbs
    {
        $this->breadcrumbs[] = $this->helper->getNetworkBreadcrumb($network);
        return $this;
    }

    public function forRoute(string $title, string $routeName, array $parameters = []): Breadcrumbs
    {
        $this->breadcrumbs[] = $this->helper->createBreadcrumbForRoute($title, $routeName, $parameters);
        return $this;
    }

    public function clear(): Breadcrumbs
    {
        $this->breadcrumbs = [];
        return $this;
    }

    public function toArray(): array
    {
        return $this->breadcrumbs;
    }
}
