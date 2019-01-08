<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Prose;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\DsShared\Helpers\FixIsiteMarkupHelper;
use App\ExternalApi\Isite\Domain\ContentBlock\Prose;

class ProsePresenter extends ContentBlockPresenter
{
    /** @var Prose */
    protected $block;

    /** @var FixIsiteMarkupHelper */
    private $fixIsiteMarkupHelper;

    /** @var string[]|null */
    private $paragraphs;

    public function __construct(
        Prose $proseBlock,
        bool $inPrimaryColumn,
        bool $isPrimaryColumnFullWith,
        FixIsiteMarkupHelper $fixMarkupHelper,
        array $options = []
    ) {
        $this->fixIsiteMarkupHelper = $fixMarkupHelper;
        parent::__construct($proseBlock, $inPrimaryColumn, $isPrimaryColumnFullWith, $options);
    }

    /**
     * Cleanup the content and split into paragraphs (Same as v2)
     * @return string[]
     */
    public function getParagraphs(): array
    {
        if (is_null($this->paragraphs)) {
            $this->paragraphs = $this->fixIsiteMarkupHelper->getParagraphs($this->block->getProse());
        }
        return $this->paragraphs;
    }

    /**
     * Only If there is more than one paragraphs, display the first one as a header.
     * The function is used to wrap the media between getHeaderParagraph() and getFooterParagraphs() when there is more
     * than one paragraphs
     *
     * @return string
     */
    public function getHeaderParagraph():string
    {
        $paragraphs = $this->getParagraphs();
        if (1 === count($paragraphs)) {
            return '';
        }
        return $paragraphs[0];
    }

    /**
     * Display the remaining paragraphs
     *
     * @return array
     */
    public function getFooterParagraphs():array
    {
        $paragraphs = $this->getParagraphs();
        if (1 === count($paragraphs)) {
            return $paragraphs;
        }
        // of there is more than 1 paragraphs, remove the header paragraphs
        array_shift($paragraphs);
        return $paragraphs;
    }
}
