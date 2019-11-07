<?php

namespace Tests\App\Controller\JSON;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Tests\App\BaseCliftonTestCase;

/**
 * @covers \App\Controller\JSON\ProgrammeChildrenController
 * @covers \App\Controller\JSON\BaseApsController
 * @IgnoreAnnotation("dataProvider")
 */
class ProgrammeChildrenControllerTest extends BaseCliftonTestCase
{
    public function testChildrenAction()
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', '/programmes/b006m86d/children.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertCount(1, $jsonContent['children']['programmes']);
        $this->assertEquals(1, $jsonContent['children']['page']);
        $this->assertEquals(0, $jsonContent['children']['offset']);
    }

    /**
     * @dataProvider childrenPaginationProvider
     */
    public function testChildrenPagination($limit, $page, $expectedPage, $expectedOffset, $expectedLimit = 50)
    {
        $this->loadFixtures(['EastendersFixture']);

        $client = static::createClient();
        $client->request('GET', sprintf('/programmes/b006m86d/children.json?page=%s&limit=%s', $page, $limit));

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertEquals($expectedPage, $jsonContent['children']['page']);
        $this->assertEquals($expectedOffset, $jsonContent['children']['offset']);
        $this->assertEquals($expectedLimit, $jsonContent['children']['limit']);
    }

    public function childrenPaginationProvider()
    {
        return [
            [ '', '', 1, 0], // Use default
            [ '-1', '-1', 1, 0], // Use default - invalid negative numbers
            [ 'a', 'a', 1, 0], // Use default - invalid alphabet inputs

            ['11', '2', 2, 20, 20], // Custom page and limit
        ];
    }

    public function testChildrenActionWithEmptyResult()
    {
        $this->loadFixtures([]);

        $client = static::createClient();
        $client->request('GET', '/programmes/qqqqqqqq/children.json');

        $this->assertResponseStatusCode($client, 404);
    }
}
