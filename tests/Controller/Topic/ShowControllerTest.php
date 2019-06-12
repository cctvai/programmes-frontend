<?php
namespace Tests\App\Controller\Topic;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

/**
 * @group topic
 */
class ShowControllerTest extends BaseWebTestCase
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
        $crawler = $this->client->request('GET', '/programmes/topics/Dank_memes');

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertNotEquals(null, $crawler->filter('h1')->getNode(0));
        $this->assertEquals('Related topics', $crawler->filter('h2.islet--vertical')->text());
        $this->assertNotEquals(null, $crawler->filter('nav > ul.tabs')->getNode(0));
        $this->assertEquals(5, count($crawler->filter('div.media.box-link.gel-long-primer.media--row')));
        $this->assertEquals('Next', $crawler->filter('span.link-complex__target.gel-pica')->text());
    }

    public function testProgrammePage()
    {
        $crawler = $this->client->request('GET', '/programmes/topics/b006q2x0/Dank_memes');

        $this->assertResponseStatusCode($this->client, 200);
        $this->assertNotEquals(null, $crawler->filter('h1')->getNode(0));
        $this->assertEquals('Related topics', $crawler->filter('h2.islet--vertical')->text());
        $this->assertNotEquals(null, $crawler->filter('nav > ul.tabs')->getNode(0));
        $this->assertEquals(5, count($crawler->filter('div.media.box-link.gel-long-primer.media--row')));
        $this->assertEquals('Next', $crawler->filter('span.link-complex__target.gel-pica')->text());
    }
}
