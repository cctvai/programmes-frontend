<?php
namespace App\DsShared;

use App\DsShared\Helpers\FixIsiteMarkupHelper;

interface FixIsiteMarkupInterface
{
    public function setFixIsiteMarkupHelper(FixIsiteMarkupHelper $helper) : void;
    public function hasFixIsiteMarkupHelper(): bool;
}
