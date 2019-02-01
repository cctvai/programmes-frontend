<?php
declare(strict_types = 1);
namespace Tests\App\DsShared;

use App\DsShared\Factory\HelperFactory;
use App\DsShared\Factory\PresenterFactory;
use App\DsShared\Utilities\ImageEntity\ImageEntityPresenter;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use RMP\Translate\Translate;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers \App\DsShared\Factory\PresenterFactory
 */
class PresenterFactoryTest extends TestCase
{
    /** @var Translate|PHPUnit_Framework_MockObject_MockObject. */
    private $translate;

    /** @var UrlGeneratorInterface|PHPUnit_Framework_MockObject_MockObject */
    private $router;

    /** @var \App\DsShared\Factory\HelperFactory|PHPUnit_Framework_MockObject_MockObject */
    private $helperFactory;

    /** @var PresenterFactory */
    private $factory;

    public function setUp()
    {
        $this->translate = $this->createMock(Translate::class);
        $translateProvider = $this->createMock(TranslateProvider::class);
        $translateProvider->method('getTranslate')->willReturn($this->translate);
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->helperFactory = $this->createMock(HelperFactory::class);
        $this->factory = new PresenterFactory();
    }

    public function testOrganismProgramme()
    {
        $mockImage = $this->createMock(Image::class);

        $this->assertEquals(
            new ImageEntityPresenter($mockImage, 240, '1'),
            $this->factory->imageEntityPresenter($mockImage, 240, '1')
        );
    }
}
