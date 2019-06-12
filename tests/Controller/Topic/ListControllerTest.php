<?php
namespace Tests\App\Controller\Topic;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

/**
 * @group topic
 */
class ListControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->loadFixtures(['ProgrammeEpisodes\BrandFixtures']);
    }

    public function testPanBbcPage()
    {
        $crawler = $this->client->request('GET', '/programmes/topics');

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertContains('Topics', $crawler->filter('h1')->text());
        $this->assertNotEquals(null, $crawler->filter('h2.lined-below.br-keyline.grid-unit')->getNode(0));
        $this->assertNotEquals(null, $crawler->filter('ol.list-inline.list-inline--spaced')->getNode(0));
        $this->assertEquals('Next', $crawler->filter('span.link-complex__target.gel-pica')->text());
    }

    public function testProgrammePage()
    {
        $crawler = $this->client->request('GET', '/programmes/b006q2x0/topics');

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertContains('B1', $crawler->filter('h1')->text());
        $this->assertContains('Topics', $crawler->filter('h1')->text());
        $this->assertNotEquals(null, $crawler->filter('h2.lined-below.br-keyline.grid-unit')->getNode(0));
        $this->assertNotEquals(null, $crawler->filter('ol.list-inline.list-inline--spaced')->getNode(0));
        $this->assertEquals('Next', $crawler->filter('span.link-complex__target.gel-pica')->text());
    }
}
