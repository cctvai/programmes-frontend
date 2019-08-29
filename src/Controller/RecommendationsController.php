<?php
declare(strict_types = 1);
namespace App\Controller;

use App\ExternalApi\RecEng\Service\RecEngService;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use GuzzleHttp\Promise\FulfilledPromise;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecommendationsController extends BaseController
{
    protected const ITEMS_PAGE = 10;  // items on main page
    protected const ITEMS_BOX = 2;    // items on includes

    public function __invoke(
        ProgrammesService $programmesService,
        string $pid,
        string $extension,
        RecEngService $recEngService,
        ProgrammesAggregationService $programmeAggregationService
    ) {
        $programme = $programmesService->findByPidFull(new Pid($pid), 'Programme');
        if (!$programme) {
            throw new NotFoundHttpException(sprintf('The item with PID "%s" was not found', $pid));
        }

        $episode = $this->getEpisode($programme, $programmeAggregationService);

        $limit = $extension ? self::ITEMS_BOX : self::ITEMS_PAGE;
        $recPromise = $episode ? $recEngService->getRecommendations($episode, $limit) : new FulfilledPromise([]);

        $viewData = function () use ($programme, $recPromise): array {
            return (['programme' => $programme] + $this->resolvePromises(['recommendations' =>  $recPromise]));
        };

        if (!$extension) {
            // recommendations page
            $this->setAtiContentId((string) $programme->getPid(), 'pips');
            $this->setAtiContentLabels('recommendations', 'recommendations');
            $this->setContextAndPreloadBranding($programme);
            return $this->renderWithChrome('recommendations/list.html.twig', $viewData());
        } else {
            // lazy-loaded partial in the page footer
            return $this->renderWithoutChrome('recommendations/show' . $extension . '.html.twig', $viewData());
        }
    }

    /**
     * recEng requires an Episode pid, so this determines which to use based on the type of Programme passed into it
     * Takes nullable args of latest, upcoming and last on episodes as this is called in TLEC controller and these are already fetched
     */
    private function getEpisode(
        Programme $programme,
        ProgrammesAggregationService $programmeAggregationService
    ): ?Episode {
        if ($programme instanceof ProgrammeContainer && $programme->getAvailableEpisodesCount()) {
            $onDemandEpisodes = $programmeAggregationService->findStreamableOnDemandEpisodes($programme, 1);
            if (!empty($onDemandEpisodes)) {
                // Theoretically if getAvailableEpisodesCount returns > 0, then we should have onDemandEpisodes
                // but cache lifetimes can mismatch.
                $onDemandEpisode = reset($onDemandEpisodes);
                if ($onDemandEpisode instanceof Episode) {
                    return $onDemandEpisode;
                }
            }
        }

        if ($programme instanceof Episode && $programme->hasPlayableDestination()) {
            return $programme;
        }

        if ($programme instanceof Clip) {
            $parent = $programme->getParent();
            if ($parent instanceof Episode && $parent->hasPlayableDestination()) {
                return $parent;
            }
        }

        return null;
    }
}
