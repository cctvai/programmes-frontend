<?php

namespace Tests\App\Controller\JSON;

use Tests\App\BaseCliftonTestCase;

/**
 * @covers \App\Controller\JSON\VersionSegmentEventsController
 */
class VersionSegmentEventControllerTest extends BaseCliftonTestCase
{
    public function testRequestVersionWithSegmentEvents()
    {
        $this->loadFixtures(['SegmentEventsFixture']);

        $client = static::createClient();
        $client->request('GET', '/programmes/v0000001/segments.json');

        $this->assertResponseStatusCode($client, 200);

        $jsonContent = $this->getDecodedJsonContent($client);

        $this->assertEquals('sv000001', $jsonContent['segment_events'][0]['pid']);
        $this->assertArrayHasKey('segment', $jsonContent['segment_events'][0]);

        $segment = $jsonContent['segment_events'][0]['segment'];
        $this->assertEquals('p002d8dd', $segment['pid']);

        $this->assertCount(1, $segment['contributions']);
    }

    public function testRequestVersionWithoutSegmentEvents404s()
    {
        $this->loadFixtures(['SegmentEventsFixture']);

        $client = static::createClient();
        $client->request('GET', '/programmes/v0000003/segments.json');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testRequestProgrammeContainer404s()
    {
        $this->loadFixtures(['MongrelsFixture']);

        $client = static::createClient();
        $client->request('GET', '/programmes/b00swyx1/segments.json');

        $this->assertResponseStatusCode($client, 404);
    }

    public function testRequestEpisodeWithAnOriginalVersionRedirects()
    {
        $this->loadFixtures(['SegmentEventsFixture']);

        $client = static::createClient();
        $client->request('GET', '/programmes/b00swgkn/segments.json');
        $this->assertRedirectTo($client, 302, '/programmes/v0000001/segments.json');
    }

    public function testRequestEpisodeWithNoOriginalVersion404s()
    {
         $this->loadFixtures(['SegmentEventsFixture']);

        $client = static::createClient();
        $client->request('GET', '/programmes/b00syxx6/segments.json');
        $this->assertResponseStatusCode($client, 404);
    }
}
