<?php
declare(strict_types = 1);
namespace Tests\App\Twig;

use App\Builders\NetworkBuilder;
use App\Twig\UrlKeyExtension;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use PHPUnit\Framework\TestCase;

class UrlKeyExtensionTest extends TestCase
{
    /**
     * @dataProvider networkLinkDataProvider
     */
    public function testNetworkLink(?string $nid, ?string $urlKey, string $expected)
    {
        if ($nid === null) {
            $network = null;
        } else {
            $network = NetworkBuilder::any()->with(['nid' => new Nid($nid), 'urlKey' => $urlKey])->build();
        }
        $extension = new UrlKeyExtension();
        $this->assertEquals($expected, $extension->networkLink($network));
    }

    public function networkLinkDataProvider()
    {
        return [
            'null_network' => [null, null, ''],
            'null_url_key' => ['bbc_arts', null, ''],
            'false' => ['parliaments_online', 'parliamentsonline', ''],
            'true' => ['bbc_one', 'bbcone', '/bbcone'],
            'custom' => ['cbeebies_radio', 'cbeebiesradio', '/cbeebies/radio'],
        ];
    }
}
