<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Utilities\PaginationLink;

use App\DsAmen\Presenter;
use RuntimeException;

class PaginationLinkPresenter extends Presenter
{
    /** @var int */
    private $page;

    /** @var string */
    private $icon;

    /** @var string */
    private $text;

    /** @var array */
    protected $options = [
        'margin' => true,
    ];

    public function __construct(int $page, string $direction, array $options = [])
    {
        parent::__construct($options);
        $this->page = $page;
        switch ($direction) {
            case 'previous':
                $this->icon = 'up';
                $this->text = 'previous';
                break;
            case 'next':
                $this->icon = 'down';
                $this->text = 'next';
                break;
            default:
                throw new RuntimeException('Pagination direction "' . $direction . '" is not allowed.');
        }
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
