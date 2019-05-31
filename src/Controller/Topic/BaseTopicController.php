<?php

declare(strict_types = 1);

namespace App\Controller\Topic;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class BaseTopicController extends BaseController
{
    protected function generateHierarchicalTitle(ProgrammeContainer $programmeContainer): string
    {
        $title = '';
        $ancestry = $programmeContainer->getAncestry();
        $maxOffset = count($ancestry) - 1;
        for ($i = $maxOffset; $i >= 0; $i--) {
            if ($i !== $maxOffset) {
                $title .= ', ';
            }
            $title .= $ancestry[$i]->getTitle();
        }
        return $title;
    }
}
