<?php
namespace Tests\App\Controller\Atoz;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

/**
 * @group atoz
 */
class IndexControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testIndexPage()
    {
        $crawler = $this->client->request('GET', '/programmes/a-z');

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertEquals('Programmes A to Z', $crawler->filter('h1')->text());
        $this->assertEquals('Search for a programme title', $crawler->filter('.search-bar__label')->text());
        $this->assertEquals(27, $crawler->filter('.letter-nav__page')->count());
    }
}
