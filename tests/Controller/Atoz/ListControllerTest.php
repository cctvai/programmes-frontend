<?php
namespace Tests\App\Controller\Atoz;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

/**
 * @group atoz
 */
class ListControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testRedirectListPage()
    {
        $this->client->request('GET', '/programmes/a-z/all');
        $this->assertRedirectTo($this->client, 301, '/programmes/a-z');
    }

    public function testRedirectSearch()
    {
        $this->client->request('GET', '/programmes/a-z/all?query=test');
        $this->assertRedirectTo($this->client, 302, '/programmes/a-z/by/test/all');
    }
}
