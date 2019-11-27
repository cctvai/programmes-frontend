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
use App\Controller\Helpers\ProducerVariableHelper;
use App\Controller\Helpers\DestinationVariableHelper;

class AtiAnalyticsLabelsTest extends TestCase
{
    public function testService()
    {
        $context = $this->serviceFactory('bbc_one', 'tv', 'BBC One');
        $labels = $this->getOrbLabels(
            $this->getProducerVariableHelperMock('BBC'),
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
                    'value' => 'BBC+One',
                ],
                [
                    'name' => 'custom_var_2',
                    'value' => null,
                ],
                [
                    'name' => 'custom_var_4',
                    'value' => 'bbc_one',
                ],
                [
                    'name' => 'custom_var_6',
                    'value' => 'false',
                ],
            ],
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    public function testBrand()
    {
        $context = BrandsFixtures::eastEnders();
        $labels = $this->getOrbLabels(
            $this->getProducerVariableHelperMock('IPLAYER'),
            $context,
            'brand',
            'brand',
            'urn:bbc:pips:b006m86d',
            ['extraLabel' => 'extraValue']
        );
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
                [
                    'name' => 'custom_var_6',
                    'value' => 'false',
                ],
            ],
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    public function testGallery()
    {
        $brand = $this->brandFactory('b006q2x0', 'Doctor Who', 'bbc_one', 'bbc_one', 'tv');
        $context = $this->galleryFactory('b0000001', 'Some Gallery', 'bbc_one', 'bbc_one', NetworkMediumEnum::TV, $brand);
        $labels = $this->getOrbLabels(
            $this->getProducerVariableHelperMock('BBC'),
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
                    'value' => 'Some+Gallery',
                ],
                [
                    'name' => 'custom_var_2',
                    'value' => null,
                ],
                [
                    'name' => 'custom_var_4',
                    'value' => 'bbc_one',
                ],
                [
                    'name' => 'custom_var_6',
                    'value' => 'false',
                ],
            ],
        ];
        $this->assertEquals($expectedLabels, $labels);
    }

    public function testIsStreamable()
    {
        $brand = $this->brandFactory('b006q2x0', 'Doctor Who', 'bbc_one', 'bbc_one', 'tv');
        $context = $this->galleryFactory('b0000001', 'Some Gallery', 'bbc_one', 'bbc_one', NetworkMediumEnum::TV, $brand);
        $labels = $this->getAnalyticsLabels(
            $this->getProducerVariableHelperMock('BBC'),
            $context,
            'article-photo-gallery',
            'gallery',
            'urn:bbc:pips:' . $context->getPid(),
            ['foo' => 'bar']
        );

        $labels->setStreamingAvailability(true);
        $orbLabels = $labels->orbLabels();
        $expected = [
            'name' => 'custom_var_6',
            'value' => 'true',
        ];
        $x16 = array_pop($orbLabels['additionalProperties']);
        $this->assertEquals($expected, $x16);

        $labels->setStreamingAvailability(false);
        $orbLabels = $labels->orbLabels();
        $expected = [
            'name' => 'custom_var_6',
            'value' => 'false',
        ];
        $x16 = array_pop($orbLabels['additionalProperties']);
        $this->assertEquals($expected, $x16);
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

    private function getProducerVariableHelperMock(string $value)
    {
        $mock = $this->createMock(ProducerVariableHelper::class);
        $mock->method('calculateProducerVariable')->willReturn($value);

        return $mock;
    }

    private function getDestinationVariableHelperMock()
    {
        $mock = $this->createMock(DestinationVariableHelper::class);
        $mock->method('getDestinationFromContext')->willReturn('programmes_ps_test');

        return $mock;
    }


    private function getAnalyticsLabels($producerHelperMock, $context, string $contentType, string $chapterOne, string $contentId, array $extraLabels = [])
    {
        return new AtiAnalyticsLabels(
            $producerHelperMock,
            $this->getDestinationVariableHelperMock(),
            $context,
            $this->getCosmosInfoMock(),
            $extraLabels,
            $contentType,
            $chapterOne,
            $contentId
        );
    }

    private function getOrbLabels(...$args)
    {
        return $this->getAnalyticsLabels(...$args)->orbLabels();
    }
}
