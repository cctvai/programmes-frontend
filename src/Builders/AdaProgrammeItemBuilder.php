<?php
namespace App\Builders;

use App\ExternalApi\Ada\Domain\AdaProgrammeItem;
use Faker\Factory;

class AdaProgrammeItemBuilder extends AbstractBuilder
{
    protected function __construct()
    {
        $faker = Factory::create();

        $this->classTarget = AdaProgrammeItem::class;

        $programme = EpisodeBuilder::any()->build();
        $relatedByClasses = [];
        for ($i = 0, $l = $faker->numberBetween(1, 10); $i < $l; $i++) {
            $relatedByClasses[] = AdaClassBuilder::any()->build();
        }

        $this->blueprintConstructorTarget = [
            'programme' => $programme,
            'pid' => $programme->getPid(),
            'title' => $programme->getTitle(),
            'programmeItemCount' => $faker->numberBetween(0, 999),
            'relatedByClasses' => $relatedByClasses,
        ];
    }
}
