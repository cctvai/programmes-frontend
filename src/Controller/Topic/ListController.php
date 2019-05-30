<?php

declare(strict_types = 1);

namespace App\Controller\Topic;

use App\Controller\BaseController;
use App\ExternalApi\Ada\Service\AdaClassService;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use RuntimeException;

class ListController extends BaseController
{
    public function __invoke(
        ?ProgrammeContainer $programmeContainer,
        AdaClassService $adaClassService
    ) {
        $page = $this->getPage();

        if ($programmeContainer === null) {
            $this->overridenDescription = 'A list of topics related to BBC programmes.';
            $this->setAtiContentLabels('list-datadriven-linkeddata', 'bbc-list-topics');
            $adaClasses = $adaClassService->findAllClasses($page, null);
            $nextPage = $adaClassService->findAllClasses($page + 1, null);
        } else {
            $this->setContextAndPreloadBranding($programmeContainer);
            $this->setAtiContentId((string) $programmeContainer->getPid());
            $this->setAtiContentLabels('list-datadriven-linkeddata', 'pid-list-topics');
            $this->overridenDescription = 'A list of topics related to '
                                        . $programmeContainer->getTitle()
                                        . ' episodes and clips.';
            $adaClasses = $adaClassService->findAllClasses($page, $programmeContainer);
            $nextPage = $adaClassService->findAllClasses($page + 1, $programmeContainer);
        }

        $resolvedPromises = $this->resolvePromises([
            'topics' => $adaClasses,
            'hasNextPage' => $nextPage,
        ]);

        if (count($resolvedPromises['topics']) < 1) {
            throw $this->createNotFoundException('No topics matched your query.');
        }

        $resolvedPromises['hasNextPage'] = count($resolvedPromises['hasNextPage']) > 0;

        return $this->renderWithChrome('topic/list.html.twig', array_merge($resolvedPromises, [
            'programmeContainer' => $programmeContainer,
            'page' => $page,
        ]));
    }
}
