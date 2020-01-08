<?php
declare(strict_types = 1);

namespace App\Controller\Contributor;

use App\Controller\BaseController;
use App\Controller\Helpers\Breadcrumbs;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\ThingsService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShowController extends BaseController
{
    public function __invoke(
        string $id,
        Breadcrumbs $breadcrumbs,
        ThingsService $thingsService,
        CoreEntitiesService $coreEntitiesService
    ) {
        $this->setAtiContentLabels('cool', 'page');

        $thing = $thingsService->findById($id);
        if (!$thing) {
            throw new NotFoundHttpException('Contributor not found');
        }

        // DO BREADCRUMBS $this->breadcrumbs = $breadcrumbs->toArray();

        return $this->renderWithChrome('contributor/show.html.twig', [
            'thing' => $thing,
            'contributions' => $coreEntitiesService->findByThing($thing),
        ]);
    }
}
