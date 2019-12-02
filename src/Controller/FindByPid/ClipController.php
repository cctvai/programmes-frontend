<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use App\Controller\Helpers\Breadcrumbs;
use App\Controller\Helpers\StructuredDataHelper;
use App\Ds2013\Factory\PresenterFactory;
use App\DsShared\Helpers\StreamableHelper;
use App\ExternalApi\Ada\Service\AdaClassService;
use App\ExternalApi\Ada\Service\AdaProgrammeService;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\ContributionsService;
use BBC\ProgrammesPagesService\Service\GroupsService;
use BBC\ProgrammesPagesService\Service\PodcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesAggregationService;
use BBC\ProgrammesPagesService\Service\RelatedLinksService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use Cake\Chronos\ChronosInterval;
use GuzzleHttp\Promise\FulfilledPromise;

class ClipController extends BaseController
{
    public function __invoke(
        AdaClassService $adaClassService,
        AdaProgrammeService $adaProgrammeService,
        Clip $clip,
        ContributionsService $contributionsService,
        GroupsService $groupsService,
        PodcastsService $podcastsService,
        PresenterFactory $presenterFactory,
        ProgrammesAggregationService $aggregationService,
        RelatedLinksService $relatedLinksService,
        SegmentEventsService $segmentEventsService,
        StreamableHelper $streamableHelper,
        StructuredDataHelper $structuredDataHelper,
        VersionsService $versionsService,
        Breadcrumbs $breadcrumbs
    ) {
        $this->setAtiContentLabels('player-clip', 'clip');
        $this->setContextAndPreloadBranding($clip);
        $this->setAtiContentId((string) $clip->getPid(), 'pips');

        $linkedVersions = $versionsService->findLinkedVersionsForProgrammeItem($clip);

        $relatedLinks = [];
        if ($clip->getRelatedLinksCount() > 0) {
            $relatedLinks = $relatedLinksService->findByRelatedToProgramme($clip, ['related_site', 'miscellaneous']);
        }

        $parentClips = [];
        $tleoClips = [];
        if ($clip->getParent()) {
            /** @var ProgrammeContainer|Episode $parent */
            $parent = $clip->getParent();
            if ($parent->getAvailableClipsCount() > 0) {
                $parentClips = $this->getClipsExcept($aggregationService, $parent, $clip->getPid());
            }
            /** @var ProgrammeContainer|Episode $tleo */
            $tleo = $clip->getTleo();
            if ($tleo && $tleo->getAvailableClipsCount() > 0 && (string) $parent->getPid() !== (string) $clip->getTleo()->getPid()) {
                $tleoClips = $this->getClipsExcept($aggregationService, $tleo, $clip->getPid());
            }
        }

        $featuredIn = $groupsService->findByCoreEntityMembership($clip, 'Collection');

        $relatedProgrammesPromise = new FulfilledPromise([]);
        $relatedTopicsPromise = new FulfilledPromise([]);
        if ($clip->getOption('show_enhanced_navigation')) {
            $relatedProgrammesPromise = $adaProgrammeService->findSuggestedByProgrammeItem($clip);
            $relatedTopicsPromise = $adaClassService->findRelatedClassesByContainer($clip, true, 10);
        }

        $segmentsListPresenter = null;
        $segmentEvents = [];
        if ($clip->getSegmentEventCount() > 0 && $linkedVersions['canonicalVersion']) {
            $segmentEvents = $segmentEventsService->findByVersionWithContributions($linkedVersions['canonicalVersion']);
        }

        $contributions = [];
        if ($clip->getContributionsCount() > 0) {
            $contributions = $contributionsService->findByContributionToProgramme($clip);
        }

        $podcast = null;
        if ($clip->getTleo() instanceof ProgrammeContainer && $clip->getTleo()->isPodcastable()) {
            $podcast = $podcastsService->findByCoreEntity($clip->getTleo());
        }

        $resolvedPromises = $this->resolvePromises([
            'relatedTopics' => $relatedTopicsPromise,
            'relatedProgrammes' => $relatedProgrammesPromise,
        ]);

        $parameters = [
            'programme' => $clip,
            'clipIsAudio' => $streamableHelper->shouldStreamViaPlayspace($clip),
            'featuredIn' => $featuredIn,
            'parentClips' => $parentClips,
            'schema' => $this->getSchema($structuredDataHelper, $clip),
            'tleoClips' => $tleoClips,
            'relatedLinks' => $relatedLinks,
            'segmentsListPresenter' => $segmentsListPresenter,
            'contributions' => $contributions,
            'podcast' => $podcast,
            'downloadableVersion' => $linkedVersions['downloadableVersion'],
            'streamableVersion' => $linkedVersions['streamableVersion'],
            'segmentEvents' => $segmentEvents,
        ];

        $this->breadcrumbs = $breadcrumbs
            ->forNetwork($clip->getNetwork())
            ->forEntityAncestry($clip)
            ->toArray();

        return $this->renderWithChrome('find_by_pid/clip.html.twig', array_merge($resolvedPromises, $parameters));
    }

    private function getSchema(
        StructuredDataHelper $structuredDataHelper,
        Clip $clip
    ): array {
        $clipSchema = $structuredDataHelper->getSchemaForClip($clip, true);

        $duration = new ChronosInterval(null, null, null, null, null, null, $clip->getDuration());
        $clipSchema['timeRequired'] = (string) $duration;

        if ($clip->getStreamableUntil()) {
            $clipSchema['expires'] = $clip->getStreamableUntil();
        }

        $genres = $clip->getGenres();
        if ($genres) {
            $clipSchema['genre'] = array_map(function ($genre) {
                return $genre->getUrlKeyHierarchy();
            }, $genres);
        }

        $videoObject = $this->getVideoObjectSchemaForClip($clip, $clipSchema);

        return $structuredDataHelper->prepare([$clipSchema, $videoObject], true);
    }

    /**
     * VideoObject is supported by google search engine
     * https://developers.google.com/search/docs/data-types/video
     *
     * @param Clip $clip
     * @param array $clipSchema
     * @return array|null
     */
    private function getVideoObjectSchemaForClip(Clip $clip, array $clipSchema): ?array
    {
        if (!$clip->getStreamableFrom()) {
            return null;
        }
        $videoObject["uploadDate"] = (string) $clip->getStreamableFrom()->format('Y-m-d');
        $videoObject["@type"] = $clip->isVideo() ? "VideoObject" : "AudioObject";
        $videoObject["name"] = $clipSchema["name"];
        $videoObject["thumbnailUrl"] = $clipSchema["image"];
        $videoObject["description"] = $clipSchema["description"];
        $videoObject["duration"] = $clipSchema["timeRequired"];
        if (isset($clipSchema['expires'])) {
            $videoObject["expires"] = $clipSchema['expires'];
        }
        if ($clip->isExternallyEmbeddable() && $clip->isVideo()) {
            $videoObject["embedUrl"] = $clipSchema["url"] . '/player';
        }

        return $videoObject;
    }

    /**
     * @param ProgrammesAggregationService $aggregationService
     * @param Programme $programme
     * @param Pid $pid
     * @return Clip[]
     */
    private function getClipsExcept(ProgrammesAggregationService $aggregationService, Programme $programme, Pid $pid): array
    {
        $clips = $aggregationService->findStreamableDescendantClips($programme, 5);
        $filteredClips = array_filter($clips, function (Clip $clip) use ($pid) {
            return (string) $clip->getPid() !== (string) $pid;
        });

        return \array_slice($filteredClips, 0, 4);
    }
}
