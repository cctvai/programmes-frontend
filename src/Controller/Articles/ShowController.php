<?php
declare(strict_types = 1);

namespace App\Controller\Articles;

use App\Controller\BaseIsiteController;
use App\Controller\Helpers\IsiteKeyHelper;
use App\Controller\Helpers\StructuredDataHelper;
use App\Exception\HasContactFormException;
use App\ExternalApi\Isite\Domain\Article;
use App\ExternalApi\Isite\Domain\ContentBlock\Telescope;
use App\ExternalApi\Isite\Service\ArticleService;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use Symfony\Component\HttpFoundation\Request;

class ShowController extends BaseIsiteController
{
    public function __invoke(
        string $key,
        string $slug,
        Request $request,
        ArticleService $isiteService,
        IsiteKeyHelper $isiteKeyHelper,
        CoreEntitiesService $coreEntitiesService,
        StructuredDataHelper $structuredDataHelper
    ) {
        $this->setIstatsProgsPageType('article_show');
        $this->setAtiContentLabels('article-show-related', 'article');

        $this->key = $key;
        $this->slug = $slug;
        $this->isiteKeyHelper = $isiteKeyHelper;
        $this->coreEntitiesService = $coreEntitiesService;
        $this->isiteService = $isiteService;

        $preview = $this->getPreview();
        if ($redirect = $this->getRedirectFromGuidToKeyIfNeeded($preview)) {
            return $redirect;
        }

        $guid = $this->isiteKeyHelper->convertKeyToGuid($this->key);
        try {
            /**  @var Article $isiteObject  */
            $isiteObject = $this->getBaseIsiteObject($guid, $preview);
        } catch (HasContactFormException $e) {
            if (!$this->slug) {
                $route = 'article_with_contact_form_noslug';
                $params = ['key' => $this->key];
            } else {
                $route = 'article_with_contact_form';
                $params = ['key' => $this->key, 'slug' => $this->slug];
            }

            return $this->cachedRedirectToRoute($route, $params, 302, 300);
        }

        if ($redirect = $this->getRedirectToSlugIfNeeded($isiteObject, $preview)) {
            return $redirect;
        }

        $this->removeHeadersForPreview($preview);
        $this->initContextAndBranding($isiteObject, $guid);
        $this->setAtiOverriddenEntityTitle($isiteObject->getTitle());
        $programme = $this->getParentProgramme($this->context);
        $parents = $isiteObject->getParents();
        $siblingPromise = $isiteService->setChildrenOn($parents, $isiteObject->getProjectSpace()); //if more than 48, extras are removed
        $childPromise = $isiteService->setChildrenOn([$isiteObject], $isiteObject->getProjectSpace(), $this->getPage());
        $response = $this->resolvePromises(['children' => $childPromise, 'siblings' => $siblingPromise]);

        $schema = $this->getSchema($structuredDataHelper, $isiteObject, $programme);

        $children = reset($response['children']);
        $paginatorPresenter = null;
        if ($children) {
            $paginatorPresenter = $this->getPaginator($children->getTotal());
        }
        return $this->renderWithChrome(
            'articles/show.html.twig',
            [
                'schema' => $schema,
                'guid' => $guid,
                'projectSpace' => $isiteObject->getProjectSpace(),
                'programme' => $programme,
                'article' => $isiteObject,
                'canDisplayVote' => $this->canDisplayVote($isiteObject->getRowGroups(), $request),
                'paginatorPresenter' => $paginatorPresenter,
            ]
        );
    }

    protected function getRouteName()
    {
        return 'programme_article';
    }


    private function getSchema(StructuredDataHelper $structuredDataHelper, Article $article, ?Programme $programme)
    {
        $schema = $structuredDataHelper->getSchemaForArticle($article, $programme);
        return $structuredDataHelper->prepare($schema);
    }
    /**
     * Return false if the user is not connected from the UK and the vote is set to "UK Only". We assume there is only
     * one telescope content block per page, if there is more than one (which is not an expected behaviour) and
     * "UK Only" is enabled the restriction will be applied to all of them.
     *
     * If the Telescope vote is set to UK Only we need to "vary" on "X-Ip_is_uk_combined" header to avoid returning
     * same cached content to non UK users when there is a vote set to UK only
     *
     * @param array $rowGroups
     * @param Request $request
     * @return bool
     */
    private function canDisplayVote(array $rowGroups, Request $request): bool
    {
        foreach ($rowGroups as $rowGroup) {
            foreach ($rowGroup->getPrimaryBlocks() as $primaryBlocks) {
                if ($primaryBlocks instanceof Telescope && $primaryBlocks->isUkOnly() === true) {
                    $this->response()->headers->set('vary', 'X-Ip_is_uk_combined');
                    if ($request->headers->get('X-Ip_is_uk_combined') === 'no') {
                        return false;
                    }
                }
            }
            foreach ($rowGroup->getSecondaryBlocks() as $secondaryBlocks) {
                if ($secondaryBlocks instanceof Telescope && $secondaryBlocks->isUkOnly() === true) {
                    $this->response()->headers->set('vary', 'X-Ip_is_uk_combined');
                    if ($request->headers->get('X-Ip_is_uk_combined') === 'no') {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
