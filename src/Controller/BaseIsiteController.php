<?php

namespace App\Controller;

use App\ExternalApi\Isite\Domain\BaseIsiteObject;
use App\ExternalApi\Isite\Domain\IsiteImage;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ImagesService;

abstract class BaseIsiteController extends BaseController
{
    protected function overrideMetaTagsValues(BaseIsiteObject $isiteObject)
    {
        $shortSynopsis = trim($isiteObject->getShortSynopsis());
        if (!empty($shortSynopsis)) {
            $this->overridenDescription = $shortSynopsis;
        }

        if ($isiteObject->getImage()) {
            $this->overridenImage = $this->imageFromPid($isiteObject->getImage());
        }
    }

    private function imageFromPid(string $imagePid)
    {
        //@TODO should article/profile objects just use this more generally
        return new IsiteImage(new Pid($imagePid));
    }
}
