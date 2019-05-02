<?php

namespace App\Ds2013\Presenters\Section\Category\Breadcrumb;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Category;

class CategoryBreadcrumbPresenter extends Presenter
{
    /** @var Category|null */
    private $category;

    /** @var string */
    private $categoryType;

    /** @var string|null */
    private $title;

    protected $options = [
        'hidden_suffix' => '',
    ];

    /**
     * @param Category|string $categoryOrTitle
     * @param string $categoryType
     * @param array $options
     */
    public function __construct($categoryOrTitle, string $categoryType = '', array $options = [])
    {
        parent::__construct($options);

        if ($categoryOrTitle instanceof Category) {
            $this->category = $categoryOrTitle;
        } else {
            $this->title = $categoryOrTitle;
        }

        $this->categoryType = $categoryType;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getCategoryType(): string
    {
        return $this->categoryType;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function isTitle(): bool
    {
        return $this->title !== null;
    }
}
