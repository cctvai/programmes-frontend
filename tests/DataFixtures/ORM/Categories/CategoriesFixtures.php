<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\ORM\Categories;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Format;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Genre;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CategoriesFixtures extends AbstractFixture
{
    /** @var ObjectManager */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $factual = $this->createGenre('C00045', 'Factual', 'factual');
        $history = $this->createGenre('C00060', 'History', 'history', $factual);
        $britishHistory = $this->createGenre('C00070', 'British History', 'britishhistory', $history);
        $this->createFormat('C00080', 'Some Format', 'someformat');
        $this->manager->flush();
    }

    private function createGenre(string $pipId, string $title, string $urlKey, ?Genre $parent = null): Genre
    {
        $genre = new Genre($pipId, $title, $urlKey);
        $genre->setParent($parent);
        $this->manager->persist($genre);
        $this->addReference($pipId, $genre);
        return $genre;
    }

    private function createFormat(string $pipId, string $title, string $urlKey): Format
    {
        $format = new Format($pipId, $title, $urlKey);
        $this->manager->persist($format);
        $this->addReference($pipId, $format);
        return $format;
    }
}
