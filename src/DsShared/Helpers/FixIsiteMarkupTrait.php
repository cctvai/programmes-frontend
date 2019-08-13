<?php
declare(strict_types = 1);
namespace App\DsShared\Helpers;

trait FixIsiteMarkupTrait
{
    /** @var FixIsiteMarkupHelper */
    protected $fixIsiteMarkupHelper;

    public function setFixIsiteMarkupHelper(FixIsiteMarkupHelper $helper) : void
    {
        $this->fixIsiteMarkupHelper = $helper;
    }

    public function hasFixIsiteMarkupHelper(): bool
    {
        return $this->fixIsiteMarkupHelper instanceof FixIsiteMarkupHelper;
    }
}
