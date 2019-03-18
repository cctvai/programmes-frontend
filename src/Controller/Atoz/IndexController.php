<?php

declare(strict_types = 1);

namespace App\Controller\Atoz;

use App\Controller\BaseController;

class IndexController extends BaseController
{
    /** @var ?string */
    protected $overridenDescription = 'An A to Z index of the BBC\'s television, radio and other programmes.';

    public function __invoke()
    {
        $this->setAtiContentLabels('list-atoz', 'atoz-homepage');

        return $this->renderWithChrome('atoz/index.html.twig');
    }
}
