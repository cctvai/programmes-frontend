<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock;

use App\Ds2013\Presenter;
use App\ExternalApi\Isite\Domain\ContentBlock\AbstractContentBlock;

abstract class ContentBlockPresenter extends Presenter
{
    /** @var AbstractContentBlock */
    protected $block;
    /** @var bool */
    protected $inPrimaryColumn;
    /** @var bool */
    protected $isPrimaryColumnFullWith;
    /** @var int */
    protected $currentHeading = 1;

    public function __construct(AbstractContentBlock $block, bool $inPrimaryColumn, bool $isPrimaryColumnFullWith, array $options = [])
    {
        parent::__construct($options);
        $this->block = $block;
        $this->inPrimaryColumn = $inPrimaryColumn;
        $this->isPrimaryColumnFullWith = $isPrimaryColumnFullWith;
    }

    public function getBlock(): AbstractContentBlock
    {
        return $this->block;
    }

    public function getTemplateVariableName(): string
    {
        return 'content_block';
    }

    public function isInPrimaryColumn(): bool
    {
        return $this->inPrimaryColumn;
    }

    public function isPrimaryColumnFullWith(): bool
    {
        return $this->isPrimaryColumnFullWith;
    }

    public function getBrandingContext(): string
    {
        if ($this->isInPrimaryColumn()) {
            return 'page';
        }
        return 'subtle';
    }

    public function getCurrentHeading(): string
    {
        return 'h' . $this->currentHeading;
    }

    public function newHeadingLevel(): string
    {
        if ($this->currentHeading < 6) {
            $this->currentHeading++;
        }

        return $this->getCurrentHeading();
    }
}
