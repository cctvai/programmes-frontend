<?php
namespace App\Builders;

use App\ExternalApi\Isite\Domain\IsiteImage;
use App\ExternalApi\Isite\Domain\Profile;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateTimeImmutable;
use Faker\Factory;
use Psr\Log\NullLogger;

class ProfileBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();
        $this->classTarget = Profile::class;

        $this->blueprintConstructorTarget = [
            'logger' => new NullLogger(),
            'title' => $faker->sentence(3),
            'key' => $faker->word,
            'fileId' => $faker->word,
            'type' => $faker->randomElement(['group', 'individual']),
            'projectSpace' => $faker->word,
            'parentPid' => new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}')),
            'shortSynopsis' => $faker->sentence(5),
            'longSynopsis' => $faker->sentence(10),
            'brandingId' => $faker->word,
            'contentBlocks' => [],
            'keyFacts' => [],
            'image' => new IsiteImage(new Pid($faker->regexify('[0-9b-df-hj-np-tv-z]{8,15}'))),
            'portraitImage' => null,
            'onwardJourneyBlock' => null,
            'tagline' => "This brand new sofa is only £399! (Other sofas are available (but aren't as good))",
            'parents' => [],
            'bbc_site' => null,
            'groupSize' => null,
            'creationDateTime' => new DateTimeImmutable(),
            'modifiedDatetime' => new DateTimeImmutable(),
        ];
    }

    public static function anyGroup()
    {
        return self::any()->with([
            'type' => 'group',
        ]);
    }

    public static function anyIndividual()
    {
        return self::any()->with([
            'type' => 'individual',
        ]);
    }
}
