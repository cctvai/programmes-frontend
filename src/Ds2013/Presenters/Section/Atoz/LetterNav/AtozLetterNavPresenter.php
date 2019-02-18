<?php

namespace App\Ds2013\Presenters\Section\Atoz\LetterNav;

use App\Ds2013\Presenter;

class AtozLetterNavPresenter extends Presenter
{
    /** @var string */
    private $current;

    /** @var string */
    private $slice;

    public function __construct(string $current, string $slice, array $options = [])
    {
        parent::__construct($options);

        $this->current = $current;
        $this->slice = $slice;
    }

    public function getCurrent(): string
    {
        return $this->current;
    }

    public function getLetters(): array
    {
        return range('a', 'z');
    }

    public function getSlice(): string
    {
        return $this->slice;
    }
}
