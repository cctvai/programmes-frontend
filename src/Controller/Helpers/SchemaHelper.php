<?php
declare(strict_types = 1);

namespace App\Controller\Helpers;

use App\DsShared\Helpers\StreamableHelper;
use App\ExternalApi\Isite\Domain\Article;
use App\ExternalApi\Isite\Domain\IsiteImage;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Recipes\Domain\Recipe;
use BBC\ProgrammesPagesService\Domain\Entity\BroadcastInfoInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Series;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\ChronosInterval;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Schema.org domain language
 * An Episode can belong to a Season and a Series
 * A Season can belong to Series
 * A Series is the top-level item
 */
class SchemaHelper
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var StreamableHelper */
    private $streamableHelper;

    public function __construct(UrlGeneratorInterface $router, StreamableHelper $streamableHelper)
    {
        $this->router = $router;
        $this->streamableHelper = $streamableHelper;
    }

    public function buildSchemaForActor(Contribution $contribution): array
    {
        return [
            '@type' => 'PerformanceRole',
            'actor' => [
                '@type' => 'Person',
                'name' => $contribution->getContributor()->getName(),
            ],
            'characterName' => $contribution->getCharacterName(),
        ];
    }

    public function getSchemaForSeries(ProgrammeContainer $programme): array
    {
        return [
            '@type' => $programme->isRadio() ? 'RadioSeries' : 'TVSeries',
            'image' => $programme->getImage()->getUrl(480),
            'description' => $programme->getShortSynopsis(),
            'identifier' => $programme->getPid(),
            'name' => $programme->getTitle(),
            'url' => $this->router->generate('find_by_pid', ['pid' => $programme->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function prepare($schemaToPrepare, $isArrayOfContexts = false): array
    {
        if ($isArrayOfContexts) {
            $schema = [
                '@context' => 'https://schema.org',
                '@graph' => $schemaToPrepare,
            ];
            return $schema;
        }

        $schemaToPrepare['@context'] = 'https://schema.org';

        return $schemaToPrepare;
    }

    public function getSchemaForEpisode(Episode $episode): array
    {
        return [
            '@type' => $episode->isRadio() ? 'RadioEpisode' : 'TVEpisode',
            'identifier' => $episode->getPid(),
            'episodeNumber' => $episode->getPosition(),
            'description' => $episode->getShortSynopsis(),
            'datePublished' => $episode->getReleaseDate(),
            'image' => $episode->getImage()->getUrl(480),
            'name' => $episode->getTitle(),
            'url' => $this->router->generate('find_by_pid', ['pid' => $episode->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function getSchemaForOnDemandEvent(Episode $episode): array
    {
        $name = null;
        $urlBroadcastService = null;

        if ($this->streamableHelper->shouldStreamViaPlayspace($episode)) {
            $name = 'BBC Sounds';
            $urlBroadcastService = 'https://www.bbc.co.uk/sounds';
        } elseif ($this->streamableHelper->shouldStreamViaIplayer($episode)) {
            $name = 'BBC iPlayer';
            $urlBroadcastService = 'https://www.bbc.co.uk/iplayer';
        } else {
            $name = 'BBC programmes';
            $urlBroadcastService = $this->router->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $event = [
            '@type' => 'OnDemandEvent',
            'publishedOn' => [
                '@type' => 'BroadcastService',
                'broadcaster' => $this->getSchemaForOrganisation(),
                'name' => $name,
                'url' => $urlBroadcastService,
            ],
            'duration' => (string) new ChronosInterval(null, null, null, null, null, null, $episode->getDuration()),
            'url' => $this->router->generate($this->streamableHelper->getRouteForProgrammeItem($episode), ['pid' => $episode->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        if ($episode->getStreamableFrom()) {
            $event['startDate'] = $episode->getStreamableFrom()->format(DATE_ATOM);
        }
        if ($episode->getStreamableUntil()) {
            $event['endDate'] = $episode->getStreamableUntil()->format(DATE_ATOM);
        }

        return $event;
    }

    public function getSchemaForOrganisation(): array
    {
        return [
            '@type' => 'Organization',
            'legalName' => 'British Broadcasting Corporation',
            'logo' => 'https://ichef.bbci.co.uk/images/ic/1200x675/p01tqv8z.png',
            'name' => 'BBC',
            'url' => 'https://www.bbc.co.uk/',
        ];
    }

    public function getSchemaForBroadcastEvent(BroadcastInfoInterface $broadcast): array
    {
        return [
            '@type' => 'BroadcastEvent',
            'startDate' => $broadcast->getStartAt()->format(DATE_ATOM),
            'endDate' => $broadcast->getEndAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @param Service|Network $service
     * @return array
     */
    public function getSchemaForService($service): array
    {
        $bbcContext = $this->getSchemaForOrganisation();

        return [
            '@type' => 'BroadcastService',
            'broadcaster' => $bbcContext,
            'name' => $service->getName(),
        ];
    }

    public function getSchemaForSeason(Series $season): array
    {
        return [
            '@type' => $season->isRadio() ? 'RadioSeason' : 'TVSeason',
            'position' => $season->getPosition(),
            'identifier' => $season->getPid(),
            'name' => $season->getTitle(),
            'url' => $this->router->generate('find_by_pid', ['pid' => $season->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function getSchemaForCollection(Collection $collection): array
    {
        return [
            '@type' => 'Collection',
            'image' => $collection->getImage()->getUrl(480),
            'description' => $collection->getShortSynopsis(),
            'identifier' => $collection->getPid(),
            'name' => $collection->getTitle(),

        ];
    }

    public function buildSchemaForClip(Clip $clip) :array
    {
        $clipSchema = [];
        $clipSchema['@type'] = $clip->isRadio() ? 'RadioClip' : 'TVClip';
        $clipSchema['identifier'] = (string) $clip->getPid();
        $clipSchema['name'] = $clip->getTitle();

        if (!is_null($clip->getReleaseDate())) {
            $clipSchema['datePublished'] = (string) $clip->getReleaseDate();
        }

        $clipSchema['url'] = $this->router->generate('find_by_pid', ['pid' => $clip->getPid()], UrlGeneratorInterface::ABSOLUTE_URL);
        $clipSchema['image'] = $clip->getImage()->getUrl(480);
        $clipSchema['description'] = $clip->getShortSynopsis();

        return $clipSchema;
    }

    public function buildSchemaForContributor(Contribution $contribution): array
    {
        $schema = [
            '@type' => 'Role',
            'contributor' => [
                '@type' => 'Person',
                'name' => $contribution->getContributor()->getName(),
            ],
            'roleName' => $contribution->getCreditRole(),
        ];

        return $schema;
    }

    public function buildSchemaForPerson(Profile $profile)
    {
        $imageUrl = null;
        if ($profile->getPortraitImage()) {
            $imageUrl = $profile->getPortraitImage()->getUrl(480);
        } elseif ($profile->getImage()) {
            $imageUrl = $profile->getImage()->getUrl(480);
        }
        $schema = [
            '@type' => 'person',
            'name' => $profile->getTitle(),
            'url' => $this->router->generate(
                'programme_profile',
                ['key' => $profile->getKey(), 'slug' => $profile->getSlug()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];
        if ($imageUrl) {
            $schema['image'] = $imageUrl;
        }
        return $schema;
    }


    public function buildSchemaForArticle(Article $article, ?Programme $programme)
    {
        $schema = [];
        $schema['@type'] = 'Article';

        if ($programme) {
            $schema['headline'] = $programme->getTitle() . ' - ' . $article->getTitle();
        } else {
            $schema['headline'] = $article->getTitle();
        }

        $image = $article->getImage();
        if ($image) {
            $schema['image'] = [
                $image->getUrl(1040, 1040),
                $image->getUrl(1920, 1080),
            ];
        }

        $organisation = $this->getSchemaForOrganisation();
        $schema['author'] = $organisation;
        $schema['publisher'] = $organisation;

        $schema['datePublished'] = $article->getCreationDateTime()->format('Y-m-d');
        $schema['dateModified'] = $article->getModifiedDateTime()->format('Y-m-d\TH:i:sP');

        return $schema;
    }

    public function buildSchemaForImage(Image $image)
    {
        $schema = [
            '@type' => 'ImageObject',
            'name' => $image->getTitle(),
            'caption' => $image->getLongestSynopsis(),
            'thumbnail' => [
                '@type' => 'ImageObject',
                'contentUrl' => $image->getUrl(224),
            ],
            'contentUrl' => $image->getUrl(976),
        ];

        return $schema;
    }

    public function buildSchemaForGallery(Gallery $gallery, ?Programme $tleoProgramme)
    {
        $tleoTitle = $tleoProgramme ? $tleoProgramme->getTitle() . ' - ' : '';
        return [
            '@type' => 'ImageGallery',
            'name' => $tleoTitle . $gallery->getTitle(),
            'description' => $gallery->getTitle(),
            'url' => $this->router->generate('find_by_pid', ['pid' => $gallery->getPid()], UrlGeneratorInterface::ABSOLUTE_URL),
            'author' => $this->getSchemaForOrganisation(),
        ];
    }

    public function getSchemaForRecipe(Recipe $recipe): array
    {
        $chef = $recipe->getChef();
        $schema = [
            '@type' => 'Recipe',
            'url' => $recipe->getUrl(),
            'name' => $recipe->getTitle(),
            'description' => $recipe->getDescription(),

        ];

        if ($recipe->getImage()) {
            $schema['image'] = $recipe->getImage()->getUrl('832');
        }

        if ($chef) {
            $schema['author'] = [
                '@type' => 'Person',
                'jobTitle' => 'Chef',
                'name' => $chef->getName(),
            ];
            if ($chef->getImage()) {
                $schema['author']['image'] = $chef->getImage()->getUrl('480');
            }
        }
        return $schema;
    }
}
