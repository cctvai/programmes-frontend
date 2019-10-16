<?php
declare(strict_types = 1);

namespace Tests\App\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;
use BBC\ProgrammesPagesService\Domain\ValueObject\UGCContactDetails;
use App\Controller\Contact\ContactController;
use App\Controller\Helpers\Breadcrumbs;
use Tests\App\BaseWebTestCase;
use function GuzzleHttp\Psr7\parse_query;

function ptrt($qs) // helper to check the ?ptrt part of UGC form link
{
    $qs = urlencode($qs);
    return "ptrt=http%3A%2F%2F%3A%2F%3F{$qs}";
}

/**
 * just render our html without booting up the whole page
 */
class ContactControllerWithoutBase extends ContactController
{
    public $twig;
    public $callback;

    public const QS = 'meh=1';

    // phpcs:ignore
    protected function setContextAndPreloadBranding($context) {}

    protected function request(): Request
    {
        $req = new Request();
        $req->server->set('QUERY_STRING', self::QS);
        return $req;
    }

    protected function renderWithChrome(string $view, array $parameters = [])
    {
        ($this->callback)($parameters);
        $innerView = 'contact/contact_markup.html.twig';
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->twig->render($innerView, $parameters);
    }
}

// phpcs:ignore
class ContactControllerTest extends BaseWebTestCase
{
    protected const UGC_CAMPAIGN_ID = 'u12345678';

    public function testController()
    {
        $contactDetails = [
            new ContactDetails('email', 'test@te.st', '9-5 only'),
            new UGCContactDetails([
                'type' => 'ugc',
                'value' => self::UGC_CAMPAIGN_ID,
                'top_nav' => false,
                'title' => 'dummy',
                'freetext' => "omgwtfbbq\n\nok",
            ]),
            new ContactDetails('twitter', '@test', "9-5 only\n10-4 on weekends"),
            new ContactDetails('address', "1 no ave\nN9 9NN", ''),
            new ContactDetails('other', "\nwhat's it mean", ''),
        ];

        $prog = $this->createMock(CoreEntity::class);
        $prog
            ->method('getOption')
            ->will($this->returnValueMap([
                ['contact_details', $contactDetails],
            ]));

        $breadcrumbs = $this->createMock(Breadcrumbs::class);

        $c = new ContactControllerWithoutBase();
        $c->twig = $this->getContainer()->get('twig');
        $test = $this;

        // TESTS
        $c->callback = function ($data) use ($test) {
            // test that the UGC contact entry is now at the top
            $details = $data['contactDetails'];
            $test->assertEquals(UGCContactDetails::class, get_class($details[0]));

            // test that the other entries did not change order
            $test->assertEquals('test@te.st', $details[1]->getValue());
            $test->assertEquals('@test', $details[2]->getValue());
        };
        $html = $c->__invoke($prog, $breadcrumbs);  // calls callback above

        $crawler = new Crawler($html);

        // test that the UGC campaign rendered first and that link is correct
        $firstHref = $crawler
            ->filter('li a')
            ->first()
            ->attr('href');

        $parts = parse_url($firstHref);

        [$nil, $send, $dest] = explode('/', $parts['path']);

        $this->assertEquals('send', $send);

        $this->assertEquals(self::UGC_CAMPAIGN_ID, $dest);
        $this->assertEquals(ptrt(ContactControllerWithoutBase::QS), $parts['query']);

        $brCount = $crawler->filter('br')->count();
        $this->assertEquals(3, $brCount);   // 0 other (whitespace), 0 twitter + 1 address + 2 UGC
    }
}
