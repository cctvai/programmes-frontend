<?php

namespace App\Ds2013\Presenters\Domain\ContentBlock\ThirdParty;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\ExternalApi\Isite\Domain\ContentBlock\ThirdParty;

class ThirdPartyPresenter extends ContentBlockPresenter
{
    /** @var ThirdParty */
    protected $block;

    public function __construct(ThirdParty $thirdPartyBlock, bool $inPrimaryColumn, bool $isPrimaryColumnFullWith, array $options = [])
    {
        parent::__construct($thirdPartyBlock, $inPrimaryColumn, $isPrimaryColumnFullWith, $options);
    }

    /**
     * @return string hostname of third party e.g. instagram.com, twitter.com etc
     */
    public function getThirdParty(): string
    {
        return $this->block->getHost();
    }
}
