<?php
namespace App\Controller\Recipes;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecipesController extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        $this->setContextAndPreloadBranding($dataForTemplate['programme']);
        $this->setAtiContentLabels('list-curated', 'recipes');

        $dataForTemplate['options'] = [
            'srcset' => [
                0 => 1/2,
                1008 => '464px',
            ],
        ];

        $programme = $this->programme;
        $this->breadcrumbs = $this->hBreadcrumbs
            ->forNetwork($programme->getNetwork())
            ->forEntityAncestry($programme)
            ->forRoute('Recipes', 'programme_recipes', ['pid' => $programme->getPid()])
            ->toArray();

        return $this->renderWithChrome('recipes/show.html.twig', $dataForTemplate);
    }

    protected function noRecipesError($pid)
    {
        throw new NotFoundHttpException(sprintf('No Recipes found for PID "%s"', $pid));
    }
}
