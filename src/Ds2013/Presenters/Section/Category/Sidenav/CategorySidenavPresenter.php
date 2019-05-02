<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Section\Category\Sidenav;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Category;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategorySidenavPresenter extends Presenter
{
    /**  @var Category */
    private $category;

    /**  @var string */
    private $categoryType;

    /** @var UrlGeneratorInterface */
    private $router;

    protected $options = [
        'active_slice' => '',
    ];

    public function __construct(UrlGeneratorInterface $router, Category $category, string $categoryType, array $options = [])
    {
        parent::__construct($options);
        $this->category = $category;
        $this->categoryType = $categoryType;
        $this->router = $router;
    }

    public function getChildren(): array
    {
        return $this->category->getChildren();
    }

    public function getSliceLink(string $slice): string
    {
        return $this->router->generate('category_slice', [
            'categoryType' => $this->categoryType,
            'categoryHierarchy' => $this->category->getUrlKeyHierarchy(),
            'slice' => $slice,
        ]);
    }

    public function getChildLink(Category $child): string
    {
        if ($this->options['active_slice']) {
            return $this->router->generate('category_slice', [
                'categoryType' => $this->categoryType,
                'categoryHierarchy' => $child->getUrlKeyHierarchy(),
                'slice' => $this->options['active_slice'],
            ]);
        }
        return $this->router->generate('category_metadata', [
            'categoryType' => $this->categoryType,
            'categoryHierarchy' => $child->getUrlKeyHierarchy(),
        ]);
    }
}
