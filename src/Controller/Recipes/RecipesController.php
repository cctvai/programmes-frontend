<?php
namespace App\Controller\Recipes;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecipesController extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        $this->setContextAndPreloadBranding($dataForTemplate['programme']);
        $this->setAtiContentLabels('recipes', 'recipes');

        $dataForTemplate['options'] = [
            'srcset' => [
                0 => 1/2,
                1008 => '464px',
            ],
        ];

        return $this->renderWithChrome('recipes/show.html.twig', $dataForTemplate);
    }

    protected function noRecipesError($pid)
    {
        throw new NotFoundHttpException(sprintf('No Recipes found for PID "%s"', $pid));
    }
}
