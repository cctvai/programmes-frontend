<?php

declare(strict_types=1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Riddle;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Riddle;

class RiddlePresenter extends ContentBlockPresenter
{
    public function __construct(Riddle $content, bool $inPrimaryColumn, bool $isPrimaryColumnFullWith, array $options = [])
    {
        parent::__construct($content, $inPrimaryColumn, $isPrimaryColumnFullWith, $options);
    }
}
