<?php
namespace Tests\App\Ds2013\Presenters\Utilities\Download;

use App\Builders\ClipBuilder;
use App\Builders\VersionBuilder;
use App\Ds2013\Presenters\Utilities\Download\DownloadPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Podcast;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPStan\Testing\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DownloadPresenterTest extends TestCase
{
    public function testConfiguration()
    {
        $givenClip = ClipBuilder::any()->build();

        $thenPresenter = $this->presenter($givenClip, null);

        $this->assertEquals('@Ds2013/Presenters/Utilities/Download/download.html.twig', $thenPresenter->getTemplatePath());
        $this->assertEquals('download', $thenPresenter->getTemplateVariableName());
    }

    /**
     * Urls to download podcast
     *
     * @dataProvider clipProvider
     */
    public function testDownloadButtonsUrlsWhenProgrammeIsDownloadable($providedClip, $expectedUrls)
    {
        $givenPresenterWithClip = $this->presenter($providedClip, null);

        $urlsToDownloadPodcast = $givenPresenterWithClip->getPodcastUrls();

        $this->assertEquals($expectedUrls, $urlsToDownloadPodcast);
    }

    public function clipProvider()
    {
        $downloadableClip = ClipBuilder::anyWithMediaSets()->build();
        $noDownloadableClip = ClipBuilder::any()->build();

        return [
            'Clip_can_be_downloaded' => [
                $downloadableClip,
                [
                    'podcast_128kbps_quality' => 'stubbed/url/from/router',
                    'podcast_64kbps_quality' => 'stubbed/url/from/router',
                ],
            ],
            'Clip_cannot_be_downloaded' => [
                $noDownloadableClip,
                [],
            ],
        ];
    }

    /**
     * @dataProvider isUkOnlyDataProvider
     */
    public function testIsUkOnly($podcast, $showIsUkOnly, $expectedResult)
    {
        $givenClip = ClipBuilder::any()->build();

        $downloadPresenter = $this->presenter($givenClip, $podcast, ['show_uk_only' => $showIsUkOnly]);
        $this->assertEquals($expectedResult, $downloadPresenter->showUkOnly());
    }

    public function isUkOnlyDataProvider()
    {
        return [
            'UK Only with show UK only' => [$this->mockPodcast(true), true, true],
            'UK Only without show UK only' => [$this->mockPodcast(true), false, false],
            'no podacst with show UK only' => [null, true, false],
        ];
    }

    /**
     * @todo: This test is migrated from DetailsPresenterTest (in episode). clean this tests.
     */
    public function testPodcastFileName()
    {
        $version = VersionBuilder::any()->build();

        $ancestors = [];
        for ($i = 'a'; $i <= 'e'; $i++) {
            $ancestor = $this->createMock(CoreEntity::class);
            $ancestor->method('getTitle')->willReturn($i);
            $ancestors[] = $ancestor;
        }

        $episode = $this->createMock(Episode::class);
        $episode->method('getAncestry')->willReturn($ancestors);
        $episode->method('getPid')->willReturn(new Pid('b000c111'));

        $presenter = new DownloadPresenter($this->createMock(UrlGeneratorInterface::class), $episode, $version, null);

        $this->assertEquals('e, d, c, b, a - b000c111.mp3', $presenter->getPodcastFileName());
    }

    /**
     * helpers
     */
    private function presenter(ProgrammeItem $programmeItem, ?Podcast $podcast, array $options = []): DownloadPresenter
    {
        $stubRouter = $this->createConfiguredMock(UrlGeneratorInterface::class, [
            'generate' => 'stubbed/url/from/router',
        ]);

        $version = VersionBuilder::any()->with(['isDownloadable' => true])->build();

        return new DownloadPresenter($stubRouter, $programmeItem, $version, $podcast, true, $options);
    }

    private function mockPodcast(bool $isUkOnly)
    {
        return $this->createConfiguredMock(Podcast::class, [
            'getIsUkOnly' => $isUkOnly,
        ]);
    }
}
