<?php
namespace App\Controller\Recipes;

class RecipesDs2013Controller extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        $dataForTemplate['options'] = [
            'srcset' => [
                0 => 1/2,
                670 => 1/4,
                1008 => '237px',
            ],
        ];

        return $this->render('recipes/show.2013inc.html.twig', $dataForTemplate, $this->response());
    }

    protected function noRecipesError($pid)
    {
        return $this->response()->setStatusCode(204)->setMaxAge(60);
    }
}
