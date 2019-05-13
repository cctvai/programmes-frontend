<?php

declare(strict_types = 1);

namespace App\Controller\Topic;

use App\Controller\BaseController;
use App\ExternalApi\Ada\Service\AdaClassService;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class ShowController extends BaseController
{
    public function __invoke(
        string $topic,
        ?string $slice,
        AdaClassService $adaClassService,
        ?ProgrammeContainer $programmeContainer
    ) {
        $this->setAtiContentLabels('ayyyyyyy', 'lmaooooooo');

        $adaClass = $adaClassService->findClassById($topic)->wait();
        if ($adaClass === null) {
            throw $this->createNotFoundException('Topic does not exist.');
            // Will also need to handle when a topic exists but not for a specific programme.
        }

        if ($programmeContainer === null) {
            $this->overridenDescription = 'A list of BBC '
                                        . ($slice !== '' ? $slice : 'programmes and clips')
                                        . ' related to "'
                                        . $adaClass->getTitle()
                                        . '".';
            $relatedAdaClasses = $adaClassService->findRelatedClassesByClass($adaClass);
        } else {
            $this->setContextAndPreloadBranding($programmeContainer);
            $this->overridenDescription = 'A list of '
                                        . $programmeContainer->getTitle()
                                        . ' episodes and clips related to "'
                                        . $adaClass->getTitle()
                                        . '".';
            $relatedAdaClasses = $adaClassService->findRelatedClassesByClassAndContainer($adaClass, $programmeContainer);
        }

        $resolvedPromises = $this->resolvePromises([
            'relatedTopics' => $relatedAdaClasses,
        ]);

        return $this->renderWithChrome('topic/show.html.twig', array_merge($resolvedPromises, [
            'topic' => $adaClass,
            'slice' => $slice,
            'programmeContainer' => $programmeContainer,
        ]));
    }
}
