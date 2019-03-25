<?php
declare (strict_types = 1);
namespace Tests\App\ValueObject;

use App\Builders\GalleryBuilder;
use App\Builders\MasterBrandBuilder;
use App\ValueObject\AtiAnalyticsLabels;
use App\ValueObject\CosmosInfo;
use Tests\App\DataFixtures\PagesService\BrandsFixtures;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Options;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Mid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class AtiAnalyticsLabelsTest extends TestCase
{
    public function testService()
    {
        $context = $this->serviceFactory('bbc_one', 'tv', 'BBC One');
        $labels = $this->getAnalyticsLabels(
            $context, // Context
            'schedule', // contentType
            'schedule-day', // chapterOne
            'urn:bbc:pips:', // contentId
            ['extraLabel' => 'extraValue'] // Extra labels
        );
        $expectedLabels = [
            'destination' => 'programmes_ps_test',
            'producer' => 'BBC',
            'contentType' => 'schedule',
            'section' => 'schedule-day',
            'contentId' => 'urn:bbc:pips:',
            'extraLabel' => 'extraValue',
            'additionalProperties' => [
                [
                    'name' => 'app_name',
                    'value' => 'programmes',
                ],
                [
                    'name' => 'custom_var_1',
                    'value' => 'BBC One',
                ],
                [
                    'name' => 'custom_var_2',
                    'value' => null,
                ],
                [
                    'name' => 'custom_var_4',
                    'value' => 'bbc_one',
                ],
            ],
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    public function testBrand()
    {
        $context = BrandsFixtures::eastEnders();
        $labels = $this->getAnalyticsLabels($context, 'brand', 'brand', 'urn:bbc:pips:b006m86d', ['extraLabel' => 'extraValue']);
        $expectedLabels = [
            'destination' => 'programmes_ps_test',
            'producer' => 'IPLAYER',
            'contentType' => 'brand',
            'section' => 'brand',
            'contentId' => 'urn:bbc:pips:b006m86d',
            'extraLabel' => 'extraValue',
            'additionalProperties' => [
                [
                    'name' => 'app_name',
                    'value' => 'programmes',
                ],
                [
                    'name' => 'custom_var_1',
                    'value' => 'EastEnders',
                ],
                [
                    'name' => 'custom_var_2',
                    'value' => 'EastEnders',
                ],
                [
                    'name' => 'custom_var_4',
                    'value' => 'bbc_one',
                ],
            ],
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    public function testGallery()
    {
        $brand = $this->brandFactory('b006q2x0', 'Doctor Who', 'bbc_one', 'bbc_one', 'tv');
        $context = $this->galleryFactory('b0000001', 'Some Gallery', 'bbc_one', 'bbc_one', NetworkMediumEnum::TV, $brand);
        $labels = $this->getAnalyticsLabels(
            $context,
            'article-photo-gallery',
            'gallery',
            'urn:bbc:pips:' . $context->getPid(),
            ['foo' => 'bar']
        );
        $expectedLabels = [
            'destination' => 'programmes_ps_test',
            'producer' => 'BBC',
            'contentType' => 'article-photo-gallery',
            'section' => 'gallery',
            'contentId' => 'urn:bbc:pips:b0000001',
            'foo' => 'bar',
            'additionalProperties' => [
                [
                    'name' => 'app_name',
                    'value' => 'programmes',
                ],
                [
                    'name' => 'custom_var_1',
                    'value' => 'Some Gallery',
                ],
                [
                    'name' => 'custom_var_2',
                    'value' => null,
                ],
                [
                    'name' => 'custom_var_4',
                    'value' => 'bbc_one',
                ],
            ],
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    private function serviceFactory(string $networkId, string $networkMedium, string $serviceName)
    {
        $service = $this->createMock(Service::class);
        if (!empty($networkId) && !empty($networkMedium)) {
            $service->method('getNetwork')->willReturn($this->networkFactory($networkId, $networkMedium));
        } else {
            $service->method('getNetwork')->willReturn(null);
        }
        $service->method('getName')->willReturn($serviceName);
        return $service;
    }

    private function brandFactory($pid, $title, $mid, $networkId, $networkMedium, $options = [])
    {
        $genre = $this->createMock(Genre::class);

        $masterBrand = $this->createMock(MasterBrand::class);
        $masterBrand->method('getMid')->willReturn(new Mid($mid));

        $brand = $this->createMock(Brand::class);
        $brand->method('getPid')->willReturn(new Pid($pid));
        $brand->method('getTitle')->willReturn(($title));
        $brand->method('getTleo')->willReturn($brand);
        $brand->method('getAncestry')->willReturn([$brand]);
        $brand->method('getGenres')->willReturn([$genre]);
        $brand->method('getMasterBrand')->willReturn($masterBrand);
        $brand->method('getPid')->willReturn(new Pid($pid));
        $brand->method('getNetwork')->willReturn($this->networkFactory($networkId, $networkMedium));
        $brand->method('getType')->willReturn('brand');
        $brand->method('isTleo')->willReturn(true);
        $brand->method('getOptions')->willReturn(new Options($options));
        $brand->method('getOption')->will(
            $this->returnCallback(function ($o) use ($brand) {
                return $brand->getOptions()->getOption($o);
            })
        );

        return $brand;
    }

    private function galleryFactory($pid, $title, $mid, $nid, $medium, $parent)
    {
        $masterBrand = MasterBrandBuilder::any()->with([
            'mid' => new Mid($mid),
            'network' => $this->networkFactory($nid, $medium),
        ])->build();
        $gallery = GalleryBuilder::any()->with([
            'pid' => new Pid($pid),
            'title' => $title,
            'parent' => $parent,
            'masterBrand' => $masterBrand,
        ])->build();
        return $gallery;
    }

    private function networkFactory(string $nid, string $medium = '')
    {
        $network = $this->createMock(Network::class);
        $network->method('getNid')->willReturn(new Nid($nid));
        if ($medium === 'tv') {
            $network->method('isTv')->willReturn(true);
        } elseif ($medium === 'radio') {
            $network->method('isRadio')->willReturn(true);
        }
        return $network;
    }

    private function getCosmosInfoMock()
    {
        $cosmosinfo = $this->createMock(CosmosInfo::class);
        $cosmosinfo->method('getAppEnvironment')->willReturn('sandbox');

        return $cosmosinfo;
    }

    private function getAnalyticsLabels($context, string $contentType, string $chapterOne, string $contentId, array $extraLabels = [])
    {
        $labelsArray = [];
        $analyticsLabels = new AtiAnalyticsLabels(
            $context,
            $this->getCosmosInfoMock(),
            $extraLabels,
            $contentType,
            $chapterOne,
            $contentId
        );
        return $analyticsLabels->orbLabels();
    }
}
