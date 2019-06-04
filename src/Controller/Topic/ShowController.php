<?php

declare(strict_types = 1);

namespace App\Controller\Topic;

use App\ExternalApi\Ada\Domain\AdaClass;
use App\ExternalApi\Ada\Service\AdaClassService;
use App\ExternalApi\Ada\Service\AdaProgrammeService;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use GuzzleHttp\Promise\PromiseInterface;

class ShowController extends BaseTopicController
{
    /** @var AdaProgrammeService */
    private $adaProgrammeService;

    /** @var string|null */
    private $slice;

    public function __invoke(
        string $topic,
        ?string $slice,
        AdaClassService $adaClassService,
        AdaProgrammeService $adaProgrammeService,
        ?ProgrammeContainer $programmeContainer
    ) {
        $this->adaProgrammeService = $adaProgrammeService;
        $this->slice = $slice;

        $adaClass = $adaClassService->findClassById($topic)->wait();
        if ($adaClass === null) {
            throw $this->createNotFoundException('Topic does not exist.');
        }

        $this->setContextAndPreloadBranding($programmeContainer);
        $page = $this->getPage();

        if ($programmeContainer === null) {
            $this->setAtiContentLabels('list-datadriven-linkeddata', 'bbc-list-programmes-topic');
            $this->overridenDescription = 'A list of BBC '
                                        . ($this->slice !== '' ? $this->slice : 'episodes and clips')
                                        . ' related to "'
                                        . $adaClass->getTitle()
                                        . '".';
            $relatedAdaClasses = $adaClassService->findRelatedClassesByClass($adaClass);
            $adaProgrammeItems = $this->adaProgrammeService->findProgrammeItemsByClass(
                $adaClass,
                $this->slice,
                $page,
                null
            );
            $videoPage = $this->getSlicePage('video', $adaClass);
            $audioPage = $this->getSlicePage('audio', $adaClass);
            $nextPage = $this->adaProgrammeService->findProgrammeItemsByClass(
                $adaClass,
                $this->slice,
                $page + 1,
                null
            );
        } else {
            $this->setAtiContentLabels('list-datadriven-linkeddata', 'pid-list-programmes-topic');
            $this->setAtiContentId((string) $programmeContainer->getPid());
            $this->overridenDescription = 'A list of '
                                        . $this->generateHierarchicalTitle($programmeContainer)
                                        . ' episodes and clips related to "'
                                        . $adaClass->getTitle()
                                        . '".';
            $relatedAdaClasses = $adaClassService->findRelatedClassesByClassAndContainer($adaClass, $programmeContainer);
            $adaProgrammeItems = $this->adaProgrammeService->findProgrammeItemsByClass(
                $adaClass,
                '',
                $page,
                $programmeContainer
            );
            $videoPage = null;
            $audioPage = null;
            $nextPage = $this->adaProgrammeService->findProgrammeItemsByClass(
                $adaClass,
                '',
                $page + 1,
                $programmeContainer
            );
        }

        $promises = [
            'relatedTopics' => $relatedAdaClasses,
            'programmes' => $adaProgrammeItems,
            'hasNextPage' => $nextPage,
        ];
        if ($videoPage !== null) {
            $promises['hasVideoPage'] = $videoPage;
        }
        if ($audioPage !== null) {
            $promises['hasAudioPage'] = $audioPage;
        }
        $resolvedPromises = $this->resolvePromises($promises);

        if (count($resolvedPromises['programmes']) < 1) {
            throw $this->createNotFoundException('No programmes matched your query.');
        }

        $resolvedPromises['hasNextPage'] = count($resolvedPromises['hasNextPage']) > 0;
        if (isset($resolvedPromises['hasVideoPage'])) {
            $resolvedPromises['hasVideoPage'] = count($resolvedPromises['hasVideoPage']) > 0;
        }
        if (isset($resolvedPromises['hasAudioPage'])) {
            $resolvedPromises['hasAudioPage'] = count($resolvedPromises['hasAudioPage']) > 0;
        }

        return $this->renderWithChrome('topic/show.html.twig', array_merge($resolvedPromises, [
            'topic' => $adaClass,
            'slice' => $this->slice,
            'programmeContainer' => $programmeContainer,
            'page' => $page,
        ]));
    }

    private function getSlicePage(
        string $slice,
        AdaClass $adaClass
    ): ?PromiseInterface {
        if ($this->slice === $slice) {
            return null;
        }

        return $this->adaProgrammeService->findProgrammeItemsByClass(
            $adaClass,
            $slice,
            1,
            null
        );
    }
}
