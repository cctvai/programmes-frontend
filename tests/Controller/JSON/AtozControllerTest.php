<?php

namespace Tests\App\Controller\JSON;

use BBC\ProgrammesPagesService\Service\AtozTitlesService;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Tests\App\BaseCliftonTestCase;

/**
 * @covers \App\Controller\JSON\AtozController
 * @IgnoreAnnotation("dataProvider")
 */
class AtozControllerTest extends BaseCliftonTestCase
{
    /**
     * @dataProvider lettersListUrlProvider
     */
    public function testLettersListAction($url, $letters, $slice)
    {
        $this->loadFixtures(['AtozTitleFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);
        unset($jsonContent['atoz']['service_group']);
        $expectedOutput = [
            'atoz' => [
                'slice' => $slice,
                'by' => null,
                'letters' => $letters,
                'page' => null,
                'limit' => null,
                'total' => null,
                'offset' => null,
                'tleo_titles' => [],
            ],
        ];
        $this->assertEquals($expectedOutput, $jsonContent);
    }

    public function lettersListUrlProvider()
    {
        return [
            ['/programmes/a-z.json', AtozTitlesService::LETTERS, 'player'],
            ['/programmes/a-z/player.json', AtozTitlesService::LETTERS, 'player'],
            ['/programmes/a-z/all.json', AtozTitlesService::LETTERS, 'all'],
        ];
    }

    /**
     * @dataProvider letterUrlProvider
     */
    public function testByLetter($url, $expectedPids)
    {
        $this->loadFixtures(['AtozTitleFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $actualPids = array_map(function ($atozTitle) {
            return $atozTitle['programme']['pid'];
        }, $jsonContent['atoz']['tleo_titles']);

        $this->assertEquals($expectedPids, $actualPids);
    }


    public function letterUrlProvider()
    {
        return [
            ['/programmes/a-z/by/@.json', ['b0000002']],
            ['/programmes/a-z/by/@/player.json', ['b0000002']],
            ['/programmes/a-z/by/m/all.json', ['b0020020', 'b010t19z']],
        ];
    }

    /**
     * @dataProvider letterUrl404Provider
     */
    public function testByLetter404($url)
    {
        $this->loadFixtures(['AtozTitleFixture']);

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCode($client, 404);
    }

    public function letterUrl404Provider()
    {
        return [
            ['/programmes/a-z/by/t.json'],
            ['/programmes/a-z/by/t/player.json'],
            ['/programmes/a-z/by/c/all.json'],
        ];
    }
}
