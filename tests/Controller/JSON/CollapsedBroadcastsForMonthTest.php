<?php

namespace Tests\App\Controller\JSON;

use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CollapsedBroadcastsService;
use Tests\App\BaseCliftonTestCase;
use DateTimeImmutable;

/**
 * @covers \App\Controller\JSON\CollapsedBroadcastLatestForProgrammeController
 */
class CollapsedBroadcastsForMonthTest extends BaseCliftonTestCase
{
    /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser */
    private $client;

    public function setUp()
    {
        $this->loadFixtures(['MongrelsFixture']);
        $this->client = static::createClient();
    }

    public function testCollapsedBroadcastsForMonth()
    {
        $container = $this->client->getContainer();
        $container->set(CollapsedBroadcastsService::class, $this->mockCollapsedBroadcastsService());

        $this->client->request('GET', "/programmes/b00swyx1/episodes/2016/10.json");

        $this->assertResponseStatusCode($this->client, 200);
        $broadcasts = $this->getDecodedJsonContent($this->client);
        $this->assertEquals(2, count($broadcasts['broadcasts']));
        $this->assertEquals('p0000001', $broadcasts['broadcasts'][0]['programme']['pid']);
        $this->assertEquals('2016-10-02', $broadcasts['broadcasts'][0]['schedule_date']);
        $this->assertEquals('p0000002', $broadcasts['broadcasts'][1]['programme']['pid']);
        $this->assertEquals('2016-10-24', $broadcasts['broadcasts'][1]['schedule_date']);
    }

    private function mockCollapsedBroadcastsService()
    {
        $bs = $this->createMock(CollapsedBroadcastsService::class);

        $bs->expects($this->once())
            ->method('findByProgrammeAndMonth')
            ->with(
                $this->callback($this->isProgrammeWithPidFn('b00swyx1')),
                2016,
                10,
                null
            )->willReturn(
                [
                    $this->createMockCollapsedBroadcastsAt('p0000001', '2016-10-02'),
                    $this->createMockCollapsedBroadcastsAt('p0000002', '2016-10-24'),
                ]
            );

        return $bs;
    }

    private function createMockCollapsedBroadcastsAt($pid, $broadcastDate)
    {
        $mockService = $this->createMock(Service::class);
        $mockService->method('getNetwork')->willReturn(
            $this->createMock('BBC\ProgrammesPagesService\Domain\Entity\Network')
        );

        $mockProgramme = $this->createMock(Episode::class);
        $mockProgramme->method('getPid')->willReturn(new Pid($pid));
        $mockProgramme->method('getFirstBroadcastDate')->willReturn(new DateTimeImmutable());

        $mockCollapsedBroadcast = $this->createMock(CollapsedBroadcast::class);
        $mockCollapsedBroadcast->method('getProgrammeItem')->willReturn($mockProgramme);
        $mockCollapsedBroadcast->method('getServices')->willReturn([$mockService]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $mockCollapsedBroadcast->method('getStartAt')->willReturn(new DateTimeImmutable($broadcastDate));
        $mockCollapsedBroadcast->method('getEndAt')->willReturn(new DateTimeImmutable());


        return $mockCollapsedBroadcast;
    }

    private function isProgrammeWithPidFn($pid)
    {
        return (function (CoreEntity $programme) use ($pid) {
            return $programme->getPid() == $pid;
        });
    }
}
