<?php
namespace Tests\App\Controller\Atoz;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

/**
 * @group atoz
 */
class ShowControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->loadFixtures(['Atoz\AtozTitleFixture']);
    }

    public function testLettersAllProgrammes()
    {
        $crawler = $this->client->request('GET', '/programmes/a-z/by/b/all');

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertEquals('B1', $crawler->filter('.programme__title > span')->text());
        $this->assertEquals('All Programmes A to Z - B', $crawler->filter('h1')->text());
        $this->assertEquals('b', $crawler->filter('.letter-nav__page > .br-box-highlight')->text());
        $this->assertEquals('All Programmes', $crawler->filter('span.atoz-slice-nav__link')->text());
    }

    public function testLettersAvailableProgrammes()
    {
        $crawler = $this->client->request('GET', '/programmes/a-z/by/b/player');

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertEquals('B1', $crawler->filter('.programme__title > span')->text());
        $this->assertEquals('Available Programmes A to Z - B', $crawler->filter('h1')->text());
        $this->assertEquals('b', $crawler->filter('.letter-nav__page > .br-box-highlight')->text());
        $this->assertEquals('Available Programmes', $crawler->filter('span.atoz-slice-nav__link')->text());
    }
}
