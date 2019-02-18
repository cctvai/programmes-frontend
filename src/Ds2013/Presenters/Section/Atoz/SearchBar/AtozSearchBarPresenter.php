<?php

namespace App\Ds2013\Presenters\Section\Atoz\SearchBar;

use App\Ds2013\Presenter;

class AtozSearchBarPresenter extends Presenter
{
    /** @var string */
    private $slice;

    /** @var string */
    private $search;

    public function __construct(string $search, string $slice, array $options = [])
    {
        parent::__construct($options);

        $this->search = $search;
        $this->slice = $slice;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function getSlice(): string
    {
        return $this->slice;
    }
}
