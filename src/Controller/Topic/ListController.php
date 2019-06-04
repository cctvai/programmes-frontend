<?php

declare(strict_types = 1);

namespace App\Controller\Topic;

use App\ExternalApi\Ada\Service\AdaClassService;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use RuntimeException;

class ListController extends BaseTopicController
{
    public function __invoke(
        ?ProgrammeContainer $programmeContainer,
        AdaClassService $adaClassService
    ) {
        $this->setContextAndPreloadBranding($programmeContainer);
        $page = $this->getPage();

        if ($programmeContainer === null) {
            $this->overridenDescription = 'A list of topics related to BBC programmes.';
            $this->setAtiContentLabels('list-datadriven-linkeddata', 'bbc-list-topics');
            $adaClasses = $adaClassService->findAllClasses($page, null);
            $nextPage = function () use ($adaClassService, $page) {
                return $adaClassService->findAllClasses($page + 1, null)->wait();
            };
        } else {
            $this->setAtiContentId((string) $programmeContainer->getPid());
            $this->setAtiContentLabels('list-datadriven-linkeddata', 'pid-list-topics');
            $this->overridenDescription = 'A list of topics related to '
                                        . $this->generateHierarchicalTitle($programmeContainer)
                                        . ' episodes and clips.';
            $adaClasses = $adaClassService->findAllClasses($page, $programmeContainer);
            $nextPage = function () use ($adaClassService, $page, $programmeContainer) {
                return $adaClassService->findAllClasses($page + 1, $programmeContainer)->wait();
            };
        }

        // $nextPage is not called in parallel to allow Ada to warmup its cache.
        $resolvedPromises = $this->resolvePromises([
            'topics' => $adaClasses,
        ]);

        if (count($resolvedPromises['topics']) < 1) {
            throw $this->createNotFoundException('No topics matched your query.');
        }

        return $this->renderWithChrome('topic/list.html.twig', array_merge($resolvedPromises, [
            'programmeContainer' => $programmeContainer,
            'page' => $page,
            'hasNextPage' => count($nextPage()) > 0,
        ]));
    }
}
