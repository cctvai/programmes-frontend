<?php
namespace Tests\App\Controller\Atoz;

use Symfony\Bundle\FrameworkBundle\Client;
use Tests\App\BaseWebTestCase;

/**
 * @group atoz
 */
class RedirectTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testRedirectCurrentList()
    {
        $this->client->request('GET', '/programmes/a-z/current');
        $this->assertRedirectTo($this->client, 301, 'http://localhost/programmes/a-z');
    }

    public function testRedirectNakedSearch()
    {
        $this->client->request('GET', '/programmes/a-z/by/a');
        $this->assertRedirectTo($this->client, 301, 'http://localhost/programmes/a-z/by/a/player');
    }

    public function testRedirectCurrentShow()
    {
        $this->client->request('GET', '/programmes/a-z/by/a/current');
        $this->assertRedirectTo($this->client, 301, 'http://localhost/programmes/a-z/by/a/player');
    }
}
