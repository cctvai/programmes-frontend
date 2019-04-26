<?php

declare(strict_types = 1);

namespace App\Controller\Food;

use App\Controller\BaseController;

class IndexController extends BaseController
{
    public function __invoke() {
        $this->setAtiContentLabels('programmes', 'rules');

        return $this->renderWithoutChrome('food/index.html.twig');
    }
}
