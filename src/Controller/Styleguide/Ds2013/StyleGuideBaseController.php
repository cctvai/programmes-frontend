<?php
declare(strict_types=1);

namespace App\Controller\Styleguide\Ds2013;

use App\Controller\BaseController;

class StyleGuideBaseController extends BaseController
{
    protected $breadcrumbs = [];

    public function __construct()
    {
        parent::__construct();
        $this->setAtiContentLabels('admin', 'styleguide');
    }
}
