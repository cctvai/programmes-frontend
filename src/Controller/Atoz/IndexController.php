<?php

declare(strict_types = 1);

namespace App\Controller\Atoz;

use App\Controller\BaseController;
use App\Controller\Helpers\Breadcrumbs;

class IndexController extends BaseController
{
    /** @var ?string */
    protected $overridenDescription = 'An A to Z index of the BBC\'s television, radio and other programmes.';

    public function __invoke(Breadcrumbs $breadcrumbs)
    {
        $this->setAtiContentLabels('list-atoz', 'atoz-homepage');

        $this->breadcrumbs = $breadcrumbs
            ->forRoute('Programmes', 'home')
            ->forRoute('A to Z', 'atoz_index')
            ->toArray();

        return $this->renderWithChrome('atoz/index.html.twig');
    }
}
