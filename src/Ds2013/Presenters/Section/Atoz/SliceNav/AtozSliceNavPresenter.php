<?php

namespace App\Ds2013\Presenters\Section\Atoz\SliceNav;

use App\Ds2013\Presenter;

class AtozSliceNavPresenter extends Presenter
{
    /** @var string */
    private $slice;

    /** @var string */
    private $search;

    public function __construct(string $search, string $slice, array $options = [])
    {
        $this->search = $search;
        $this->slice = $slice;

        parent::__construct($options);
    }

    public function getSlice(): string
    {
        return $this->slice;
    }

    public function getSearch(): string
    {
        return $this->search;
    }
}
