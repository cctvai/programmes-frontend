<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite\Service;

use App\ExternalApi\Client\HttpApiClientFactory;
use App\ExternalApi\Client\HttpApiMultiClient;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Isite\IsiteFeedResponseHandler;
use App\ExternalApi\Isite\IsiteResult;
use App\ExternalApi\Isite\Service\IsiteService;
use BBC\ProgrammesCachingLibrary\CacheInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class IsiteServiceTest extends TestCase
{
    public function testGetHttpApiMultiClientIsCalledWithCorrecUrl()
    {
        $url = 'baseurl/search?q=%7B%22namespaces%22%3A%7B%22profile%22%3A%22https%3A%5C%2F%5C%2Fproduction.bbc.co.uk%5C%2Fisite2%5C%2Fproject%5C%2Fproject-space%5C%2Fprogrammes-profile%22%7D%2C%22project%22%3A%22project-space%22%2C%22depth%22%3A0%2C%22sort%22%3A%5B%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Aposition%22%2C%22type%22%3A%22numeric%22%2C%22direction%22%3A%22asc%22%7D%2C%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Atitle%22%2C%22direction%22%3A%22asc%22%7D%5D%2C%22page%22%3A%221%22%2C%22pageSize%22%3A%2248%22%2C%22query%22%3A%7B%22and%22%3A%5B%5B%22profile%3Aparent_pid%22%2C%22%3D%22%2C%22bcdfghjk%22%5D%2C%7B%22not%22%3A%5B%5B%22profile%3Aparent%22%2C%22contains%22%2C%22urn%3Aisite%22%5D%5D%7D%5D%7D%7D';
        $mockProgramme = $this->createMock(Programme::class);
        $mockProgramme->expects($this->once())
            ->method('getOption')
            ->with('project_space')
            ->willReturn('project-space');
        $mockProgramme->method('getPid')
            ->willReturn(new Pid('bcdfghjk'));
        $mockClient = $this->createMock(HttpApiMultiClient::class);
        $mockHttpApiClientFactory = $this->createMock(HttpApiClientFactory::class);
        $mockHttpApiClientFactory->expects($this->once())
            ->method('keyHelper')
            ->willReturn('cacheKey');
        $mockHttpApiClientFactory->expects($this->once())
            ->method('getHttpApiMultiClient')
            ->with('cacheKey', [$url], function () {
            }, [], [], CacheInterface::NORMAL, CacheInterface::NONE, ['timeout' => 10])
            ->willReturn($mockClient);
        $service = new IsiteService('baseurl', $mockHttpApiClientFactory, $this->createMock(IsiteFeedResponseHandler::class));
        $service->getProfilesByProgramme($mockProgramme);
    }

    public function testGetHttpApiMultiClientIsCalledWithCorrectNumberOfUrlsAndProfiles()
    {
        $profileA = $this->createMock(Profile::class);
        $profileA->method('getFileId')->willReturn('fileIdA');
        $profileB = $this->createMock(Profile::class);
        $profileB->method('getFileId')->willReturn('fileIdB');
        $profileC = $this->createMock(Profile::class);
        $profileC->method('getFileId')->willReturn('fileIdC');
        $urls = [
            'baseurl/search?q=%7B%22namespaces%22%3A%7B%22profile%22%3A%22https%3A%5C%2F%5C%2Fproduction.bbc.co.uk%5C%2Fisite2%5C%2Fproject%5C%2Fproject-space%5C%2Fprogrammes-profile%22%7D%2C%22project%22%3A%22project-space%22%2C%22depth%22%3A0%2C%22sort%22%3A%5B%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Aposition%22%2C%22type%22%3A%22numeric%22%2C%22direction%22%3A%22asc%22%7D%2C%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Atitle%22%2C%22direction%22%3A%22asc%22%7D%5D%2C%22page%22%3A%221%22%2C%22pageSize%22%3A%2248%22%2C%22query%22%3A%5B%22profile%3Aparent%22%2C%22%3D%22%2C%22urn%3Aisite%3Aproject-space%3AfileIdA%22%5D%7D',
            'baseurl/search?q=%7B%22namespaces%22%3A%7B%22profile%22%3A%22https%3A%5C%2F%5C%2Fproduction.bbc.co.uk%5C%2Fisite2%5C%2Fproject%5C%2Fproject-space%5C%2Fprogrammes-profile%22%7D%2C%22project%22%3A%22project-space%22%2C%22depth%22%3A0%2C%22sort%22%3A%5B%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Aposition%22%2C%22type%22%3A%22numeric%22%2C%22direction%22%3A%22asc%22%7D%2C%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Atitle%22%2C%22direction%22%3A%22asc%22%7D%5D%2C%22page%22%3A%221%22%2C%22pageSize%22%3A%2248%22%2C%22query%22%3A%5B%22profile%3Aparent%22%2C%22%3D%22%2C%22urn%3Aisite%3Aproject-space%3AfileIdB%22%5D%7D',
            'baseurl/search?q=%7B%22namespaces%22%3A%7B%22profile%22%3A%22https%3A%5C%2F%5C%2Fproduction.bbc.co.uk%5C%2Fisite2%5C%2Fproject%5C%2Fproject-space%5C%2Fprogrammes-profile%22%7D%2C%22project%22%3A%22project-space%22%2C%22depth%22%3A0%2C%22sort%22%3A%5B%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Aposition%22%2C%22type%22%3A%22numeric%22%2C%22direction%22%3A%22asc%22%7D%2C%7B%22elementPath%22%3A%22%5C%2Fprofile%3Aform%5C%2Fprofile%3Ametadata%5C%2Fprofile%3Atitle%22%2C%22direction%22%3A%22asc%22%7D%5D%2C%22page%22%3A%221%22%2C%22pageSize%22%3A%2248%22%2C%22query%22%3A%5B%22profile%3Aparent%22%2C%22%3D%22%2C%22urn%3Aisite%3Aproject-space%3AfileIdC%22%5D%7D',
        ];
        $mockClient = $this->createMock(HttpApiMultiClient::class);
        $mockHttpApiClientFactory = $this->createMock(HttpApiClientFactory::class);
        $mockHttpApiClientFactory->expects($this->once())
            ->method('keyHelper')
            ->willReturn('cacheKey');
        $mockHttpApiClientFactory->expects($this->once())
            ->method('getHttpApiMultiClient')
            ->with('cacheKey', $urls, function () {
            }, [[$profileA, $profileB, $profileC]], [], CacheInterface::NORMAL, CacheInterface::NONE, ['timeout' => 10])
            ->willReturn($mockClient);
        $service = new IsiteService('baseurl', $mockHttpApiClientFactory, $this->createMock(IsiteFeedResponseHandler::class));
        $service->getChildrenOfProfiles('project-space', [$profileA, $profileB, $profileC]);
    }

    public function testResponseHandlerOfGetChildrenOfProfilesResponses()
    {
        $someArrayA = ['foo', 'bar'];
        $someArrayB = ['baz', 'qux'];
        $responseA = $this->createMock(Response::class);
        $responseB = $this->createMock(Response::class);
        $profileA = $this->createMock(Profile::class);
        $profileA->expects($this->once())
            ->method('setChildren')
            ->with($someArrayA);
        $profileB = $this->createMock(Profile::class);
        $profileB->expects($this->once())
            ->method('setChildren')
            ->with($someArrayB);
        $iSiteResultA = $this->createMock(IsiteResult::class);
        $iSiteResultA->expects($this->once())
            ->method('getDomainModels')
            ->willReturn($someArrayA);
        $iSiteResultB = $this->createMock(IsiteResult::class);
        $iSiteResultB->expects($this->once())
            ->method('getDomainModels')
            ->willReturn($someArrayB);
        $mockResponseHandler = $this->createMock(IsiteFeedResponseHandler::class);
        $mockResponseHandler->expects($this->exactly(2))
            ->method('getIsiteResult')
            ->withConsecutive($responseA, $responseB)
            ->willReturnOnConsecutiveCalls($iSiteResultA, $iSiteResultB);
        $service = new IsiteService('baseurl', $this->createMock(HttpApiClientFactory::class), $mockResponseHandler);
        $service->parseChildrenOfProfilesResponses([$responseA, $responseB], [$profileA, $profileB]);
    }

    public function testResponseHandlerOfGetProfilesByProgramme()
    {
        $response = $this->createMock(Response::class);
        $iSiteResult = $this->createMock(IsiteResult::class);
        $mockResponseHandler = $this->createMock(IsiteFeedResponseHandler::class);
        $mockResponseHandler->expects($this->once())
            ->method('getIsiteResult')
            ->with($response)
            ->willReturn($iSiteResult);
        $service = new IsiteService('baseurl', $this->createMock(HttpApiClientFactory::class), $mockResponseHandler);
        $this->assertEquals($iSiteResult, $service->parseProfilesByProgrammeResponses([$response]));
    }
}