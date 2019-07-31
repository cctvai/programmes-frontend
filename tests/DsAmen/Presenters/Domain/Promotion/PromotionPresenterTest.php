<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Presenters\Domain\Promotion;

use App\DsAmen\Presenters\Domain\Promotion\PromotionPresenter;
use App\DsShared\Factory\HelperFactory;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PromotionPresenterTest extends TestCase
{
    private $mockRouter;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testPromotionDefaults()
    {
        $relatedLinks = [$this->createMock(RelatedLink::class)];

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getTitle' => 'title',
            'getShortSynopsis' => 'short synopsis',
            'getRelatedLinks' => $relatedLinks,
        ]);

        $helperFactory = $this->createMock(HelperFactory::class);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, $helperFactory);

        $this->assertSame('title', $presenter->getTitle());
        $this->assertSame('short synopsis', $presenter->getSynopsis());
        $this->assertSame($relatedLinks, $presenter->getRelatedLinks());
        $this->assertSame('br-box-subtle', $presenter->getBrandingBoxClass());
        $this->assertSame('br-subtle-text-ontext', $presenter->getTextBrandingClass());
    }

    public function testPromotionOfProgrammeItem()
    {
        $this->mockRouter->method('generate')
            ->with('find_by_pid', ['pid' => 'b0000001'])
            ->willReturn('/programmes/b0000001');

        $image = $this->createMock(Image::class);

        $promotedEntity = $this->createConfiguredMock(Episode::class, [
            'getPid' => new Pid('b0000001'),
            'getImage' => $image,
            'hasPlayableDestination' => false,
            'isTv' => false,
        ]);

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
        ]);

        $helperFactory = $this->createMock(HelperFactory::class);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, $helperFactory);
        $this->assertSame('/programmes/b0000001', $presenter->getUrl());
        $this->assertSame($image, $presenter->getImage());
    }

    public function testPromotionOfStreamableProgrammeItem()
    {
        $this->mockRouter->method('generate')
            ->with('find_by_pid', ['pid' => 'b0000001'])
            ->willReturn('/programmes/b0000001');

        $image = $this->createMock(Image::class);

        $promotedEntity = $this->createConfiguredMock(Episode::class, [
            'getPid' => new Pid('b0000001'),
            'getImage' => $image,
            'hasPlayableDestination' => true,
            'isTv' => true,
        ]);

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
        ]);

        $helperFactory = $this->createMock(HelperFactory::class);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, $helperFactory);
        $this->assertSame('/programmes/b0000001', $presenter->getUrl());
        $this->assertSame($image, $presenter->getImage());
    }

    public function testPromotionOfImage()
    {
        $promotedEntity = $this->createMock(Image::class);

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
            'getUrl' => 'http://example.com',
        ]);

        $helperFactory = $this->createMock(HelperFactory::class);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, $helperFactory);

        $this->assertSame('http://example.com', $presenter->getUrl());
        $this->assertSame($promotedEntity, $presenter->getImage());
        // external link should display CTA External link icon
        $this->assertTrue($presenter->shouldDisplayCta());

        // Test internal link
        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getPromotedEntity' => $promotedEntity,
            'getUrl' => 'http://bbc.co.uk/internal',
        ]);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, $helperFactory);
        $this->assertSame('http://bbc.co.uk/internal', $presenter->getUrl());
        // internal link doesn't show CTA
        $this->assertFalse($presenter->shouldDisplayCta());
    }

    public function testFilteringRelatedLinks()
    {
        $mockRelatedLinks = [
            $this->createMock(RelatedLink::class),
            $this->createMock(RelatedLink::class),
            $this->createMock(RelatedLink::class),
        ];

        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getRelatedLinks' => $mockRelatedLinks,
        ]);

        $helperFactory = $this->createMock(HelperFactory::class);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, $helperFactory, [
            'show_synopsis' => false,
            'show_image' => false,
            'related_links_count' => 1,
        ]);

        $this->assertSame('', $presenter->getSynopsis());
        $this->assertSame([$mockRelatedLinks[0]], $presenter->getRelatedLinks());
    }

    public function testDisabledOptions()
    {
        $promotion = $this->createConfiguredMock(Promotion::class, [
            'getTitle' => 'title',
            'getShortSynopsis' => 'short synopsis',
            'getPromotedEntity' => $this->createMock(Image::class),
            'getRelatedLinks' => [$this->createMock(RelatedLink::class)],
        ]);

        $helperFactory = $this->createMock(HelperFactory::class);

        $presenter = new PromotionPresenter($this->mockRouter, $promotion, $helperFactory, [
            'show_synopsis' => false,
            'show_image' => false,
            'related_links_count' => 0,
        ]);

        $this->assertSame('', $presenter->getSynopsis());
        $this->assertSame([], $presenter->getRelatedLinks());
        $this->assertNull($presenter->getImage());
    }

    public function testInvalidRelatedLinksCount()
    {
        $promotion = $this->createMock(Promotion::class);

        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('related_links_count option must 0 or a positive integer');

        $helperFactory = $this->createMock(HelperFactory::class);

        new PromotionPresenter($this->mockRouter, $promotion, $helperFactory, [
            'related_links_count' => -1,
        ]);
    }
}
