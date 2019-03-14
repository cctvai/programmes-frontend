<?php

namespace Tests\App\DataFixtures\ORM\Atoz;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\AtozTitle;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Tests\App\DataFixtures\ORM\ProgrammeEpisodes\BrandFixtures;

class AtozTitleFixture extends AbstractFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [BrandFixtures::class];
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist(new AtozTitle('B1', $this->getReference('b006q2x0')));
        $manager->flush();
    }
}
