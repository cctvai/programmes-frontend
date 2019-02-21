<?php
declare(strict_types = 1);
namespace App\Controller\Styleguide\Amen;

use App\Controller\Styleguide\Ds2013\StyleGuideBaseController;

class IntroController extends StyleGuideBaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('styleguide/amen/intro.html.twig');
    }
}
