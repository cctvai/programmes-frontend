<?php
declare(strict_types = 1);

namespace Tests\App\Controller\Categories;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;
use Tests\App\BaseWebTestCase;

class ListingControllerTest extends BaseWebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->loadFixtures(['ProgrammeEpisodes\\BrandFixtures']);
        $this->client = static::createClient();
    }

    public function testListAllForFormat()
    {
        $crawler = $this->client->request('GET', '/programmes/formats/someformat/all');

        $this->assertResponseStatusCode($this->client, 200);
        // Assert correct number of programmes
        $this->assertEquals(2, $crawler->filter('.programmes-page .programme')->count());

        // Assert correct programme titles (ignoring order)
        $this->assertProgrammeTitles(['B1', 'B2'], $crawler);

        // Assert correct category title
        $this->assertEquals(
            'Some Format',
            $crawler->filter('.categories-breadcrumb .categories-breadcrumb__item')->eq(1)->text()
        );
        // Assert masterbrand
        $this->assertEquals('RADIOMASTER', $crawler->filter('.programme__service')->eq(1)->text());
    }

    public function testListAvailableForGenre()
    {
        $crawler = $this->client->request('GET', '/programmes/genres/factual/player');
        //echo $crawler->html();die();
        $this->assertResponseStatusCode($this->client, 200);
        // Assert correct number of programmes
        $this->assertEquals(2, $crawler->filter('.programmes-page .programme')->count());
        // Assert correct programme titles (ignoring order)
        $this->assertProgrammeTitles(['B1', 'B3'], $crawler);
        // Assert sub-genre shown
        $childCategoryLinks = $crawler->filter('.categories-navigation-filter-by__options .category');
        $this->assertEquals(1, $childCategoryLinks->count());
        $childTitle = preg_replace('/\s/', '', $childCategoryLinks->text()); // Strip whitespace
        $this->assertEquals('History', $childTitle);
        // Assert description
        $this->assertEquals(
            'List of available BBC programmes categorised as "Factual".',
            $crawler->filter('meta[name="description"]')->attr('content')
        );
    }

    private function assertProgrammeTitles(array $expectedTitles, Crawler $crawler): void
    {
        $titlesIterator = $crawler->filter('.programmes-page .programme .programme__title')->getIterator();
        $titles = [];
        foreach ($titlesIterator as $titleElement) {
            $titles[] = $titleElement->textContent;
        }
        sort($titles);
        $this->assertEquals($expectedTitles, $titles);
    }
}
