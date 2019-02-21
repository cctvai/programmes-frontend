<?php
declare(strict_types=1);
namespace App\Controller\Styleguide\Ds2013\Utilities;

use App\Controller\Styleguide\Ds2013\StyleGuideBaseController;

class PaginationController extends StyleGuideBaseController
{
    public function __invoke()
    {
        parent::__construct();
        return $this->renderWithChrome('styleguide/ds2013/utilities/pagination.html.twig');
    }
}
