<?php
declare(strict_types = 1);
namespace Tests\App\DsAmen;

use App\DsAmen\Factory\PresenterFactory;
use App\DsAmen\Presenters\Domain\CoreEntity\Programme\ProgrammePresenter;
use App\DsShared\Factory\HelperFactory;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @covers \App\DsAmen\Factory\PresenterFactory
 */
class PresenterFactoryTest extends TestCase
{
    /** @var TranslatorInterface|PHPUnit_Framework_MockObject_MockObject. */
    private $translator;

    /** @var UrlGeneratorInterface|PHPUnit_Framework_MockObject_MockObject */
    private $router;

    /** @var HelperFactory|PHPUnit_Framework_MockObject_MockObject */
    private $helperFactory;

    /** @var PresenterFactory */
    private $factory;

    public function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);
        $this->helperFactory = $this->createMock(HelperFactory::class);
        $this->factory = new PresenterFactory($this->translator, $this->router, $this->helperFactory);
    }

    public function testOrganismProgramme()
    {
        $mockProgramme = $this->createMock(Programme::class);

        $this->assertEquals(
            new ProgrammePresenter($mockProgramme, $this->router, $this->helperFactory, ['opt' => 'foo']),
            $this->factory->programmePresenter($mockProgramme, ['opt' => 'foo'])
        );
    }
}
