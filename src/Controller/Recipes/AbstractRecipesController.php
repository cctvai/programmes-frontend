<?php
declare(strict_types=1);

namespace App\Controller\Recipes;

use App\Controller\BaseController;
use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use App\ExternalApi\Recipes\Service\RecipesService;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Controller\Helpers\StructuredDataHelper;

abstract class AbstractRecipesController extends BaseController
{
    public function __invoke(Programme $programme, RecipesService $recipesService, StructuredDataHelper $structuredDataHelper)
    {
        $pid = (string) $programme->getPid();
        if (!$programme->getOption('recipes_enabled')) {
            throw new NotFoundHttpException(sprintf('Unknown Recipes with PID "%s"', $pid));
        }

        $apiResponse = $recipesService->fetchRecipesByPid($pid)->wait(true);
        // if there are no recipes, don't display anything
        if ($apiResponse->getTotal() === 0 || !$apiResponse->getRecipes()) {
            return $this->noRecipesError($pid);
        }

        $showImage = true;

        // Only show image if all recipes have images. Otherwise, the cards without images look very weird
        foreach ($apiResponse->getRecipes() as $recipe) {
            $showImage &= $recipe->getImage() ? true : false;
        }

        // Cache for 5 minutes
        $this->response()->setMaxAge(300);

        $this->overridenDescription = 'Recipes from ' . $programme->getTitle();
        $recipes = $apiResponse->getRecipes();
        $schema = $this->getSchema($structuredDataHelper, $recipes, $programme);
        return $this->renderRecipes([
            'recipes' => $recipes,
            'total' => $apiResponse->getTotal(),
            'pid' => $pid,
            'showImage' => $showImage,
            'programme' => $programme,
            'schema' => $schema,
        ]);
    }

    abstract protected function noRecipesError($pid);

    abstract protected function renderRecipes(array $dataForTemplate);

    private function getSchema(StructuredDataHelper $structuredDataHelper, array $recipes, Programme $programme)
    {
        $recipeArray = [];
        foreach ($recipes as $recipe) {
            $recipeArray[] = $structuredDataHelper->getSchemaForRecipe($recipe);
        }

        $schema = $structuredDataHelper->getSchemaForCoreEntity($programme);
        $schema['hasPart'] = $recipeArray;
        return $structuredDataHelper->prepare($schema);
    }
}
