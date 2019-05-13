<?php
declare(strict_types = 1);
namespace Tests\App\ArgumentResolver;

use App\ArgumentResolver\CategoryByUrlKeyValueResolver;
use BBC\ProgrammesPagesService\Domain\Entity\Category;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Service\CategoriesService;
use BBC\ProgrammesPagesService\Service\ServiceFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;

class CategoryByUrlKeyValueResolverTest extends TestCase
{
    /** @var ArgumentResolver */
    private $resolver;

    /** @var CategoriesService|PHPUnit_Framework_MockObject_MockObject */
    private $categoriesService;


    public function setUp()
    {
        $this->categoriesService = $this->createMock(CategoriesService::class);

        /** @var ServiceFactory|PHPUnit_Framework_MockObject_MockObject $serviceFactory */
        $serviceFactory = $this->createMock(ServiceFactory::class);
        $serviceFactory->method('getCategoriesService')->willReturn($this->categoriesService);

        $this->resolver = new ArgumentResolver(null, [
            new CategoryByUrlKeyValueResolver($this->categoriesService),
        ]);
    }

    /**
     * @dataProvider genresDataProvider
     */
    public function testResolveGenres($categoryHierarcy, $serviceArguments)
    {
        $request = Request::create('/');
        $request->attributes->set('categoryType', 'genres');
        $request->attributes->set('categoryHierarchy', $categoryHierarcy);
        $mockController = function (Category $category) {
        };

        $category = $this->createMock(Genre::class);

        $this->categoriesService->expects($this->once())
            ->method('findGenreByUrlKeyAncestryWithDescendants')
            ->with($serviceArguments)
            ->willReturn($category);

        $this->assertEquals(
            [$category],
            $this->resolver->getArguments($request, $mockController)
        );
    }

    public function genresDataProvider()
    {
        return [
            'Simple top level genre' => ['comedy', ['comedy']],
            'Second level genre' => ['comedy/standup', ['standup', 'comedy']],
            'Third level genre' => ['comedy/standup/bad', ['bad', 'standup', 'comedy']],
        ];
    }

    public function testResolveFormats()
    {
        $request = Request::create('/programmes/formats/films');
        $request->attributes->set('categoryType', 'formats');
        $request->attributes->set('categoryHierarchy', 'films');
        $mockController = function (Category $category) {
        };

        $category = $this->createMock(Format::class);

        $this->categoriesService->expects($this->once())
            ->method('findFormatByUrlKeyAncestry')
            ->with('films')
            ->willReturn($category);

        $this->assertEquals(
            [$category],
            $this->resolver->getArguments($request, $mockController)
        );
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function test404()
    {
        $request = Request::create('/programmes/formats/thistotallyexists');
        $request->attributes->set('categoryType', 'formats');
        $request->attributes->set('categoryHierarchy', 'thistotallyexists');
        $mockController = function (Category $category) {
        };

        $this->categoriesService->expects($this->once())
            ->method('findFormatByUrlKeyAncestry')
            ->with('thistotallyexists')
            ->willReturn(null);

        $this->resolver->getArguments($request, $mockController);
    }
}
