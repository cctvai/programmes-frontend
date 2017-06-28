<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;

class BrandsFixtures
{
    public static function eastEnders(): Brand
    {
        return new Brand(
            [1],
            new Pid('b006m86d'),
            'EastEnders',
            'EastEnders',
            new Synopses(
                'Welcome to Walford, E20.',
                'Welcome to Walford, E20.'
            ),
            ImagesFixtures::eastenders(),
            0, // promotions count
            0, //related links count
            false, //hasSupportingContent
            true, // isStreamable
            true, // isStreamableAlternate
            0, //ContributionsCount
            0, //agg broadcasts count
            5, // aggregated episodes count
            0, //available clips count
            1, //available episodes count
            0, //available galleries count
            false,
            null, //parent
            null, //position
            MasterBrandsFixtures::bbcOne(),
            null, //genres
            null, //formats
            null, //first broadcast date
            5 //expected child count
        );
    }

    public static function bookOfTheWeek()
    {
        return new Brand(
            [2],
            new Pid('b006qftk'),
            'Book of the Week',
            'Book of the Week',
            new Synopses(
                'Serialised book readings, featuring works from various genres',
                'Serialised book readings, featuring works of non-fiction, biography, autobiography, travel, diaries, essays, humour and history'
            ),
            ImagesFixtures::bookOfTheWeek(),
            0, // promotions count
            0, //related links count
            false, //hasSupportingContent
            true, // isStreamable
            false, // isStreamableAlternate
            0, //ContributionsCount
            0, //agg broadcasts count
            500, // aggregated episodes count
            0, //available clips count
            30, //available episodes count
            0, //available galleries count
            false,
            null, //parent
            null, //position
            MasterBrandsFixtures::radioFour(),
            null, //genres
            null, //formats
            null, //first broadcast date
            null //expected child count
        );
    }
}
