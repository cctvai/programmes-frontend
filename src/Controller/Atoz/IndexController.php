<?php

declare(strict_types = 1);

namespace App\Controller\Atoz;

use App\Controller\BaseController;

class IndexController extends BaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('atoz/index.html.twig');
    }
}
