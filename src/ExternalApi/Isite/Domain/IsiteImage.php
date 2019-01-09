<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Domain;

use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class IsiteImage extends Image
{
    public function __construct(Pid $pid)
    {
        parent::__construct($pid, '', '', '', '', 'jpg');
    }
}
