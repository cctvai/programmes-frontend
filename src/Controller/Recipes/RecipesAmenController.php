<?php
namespace App\Controller\Recipes;

class RecipesAmenController extends AbstractRecipesController
{
    protected function renderRecipes(array $dataForTemplate)
    {
        return $this->render('recipes/show.ameninc.html.twig', $dataForTemplate, $this->response());
    }

    protected function noRecipesError($pid)
    {
        return $this->response()->setStatusCode(204)->setMaxAge(60);
    }
}
