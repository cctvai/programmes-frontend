<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Presenters\Section\TopicList;

use App\Builders\AdaClassBuilder;
use App\DsAmen\Presenters\Section\TopicList\TopicListPresenter;
use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use PHPUnit\Framework\TestCase;

class TopicListPresenterTest extends TestCase
{
    /** @dataProvider mapTopicsProvider */
    public function testMapTopics(array $expected, array $adaClasses)
    {
        $topics = [];
        foreach ($adaClasses as $adaClass) {
            $topics[] = AdaClassBuilder::any()->with([
                'id' => $adaClass[0],
                'title' => $adaClass[1],
            ])->build();
        }
        $topicsPresenter = new TopicListPresenter($topics, null, [
            'show_letter_headings' => true,
        ]);
        $titleMap = [];
        foreach ($topicsPresenter->getTopics() as $letter => $letterTopics) {
            foreach ($letterTopics as $topic) {
                $titleMap[$letter][] = $topic->getId();
            }
        }
        $this->assertEquals($expected, $titleMap);
    }

    public function mapTopicsProvider(): array
    {
        return [
            'uppercase' => [
                [
                    'A' => ['an_ada_class'],
                    'Z' => ['ze_ada_class'],
                ],
                [
                    ['an_ada_class', 'An Ada Class'],
                    ['ze_ada_class', 'Ze Ada Class'],
                ],
            ],
            'lowercase' => [
                [
                    'A' => ['an_ada_class'],
                    'Z' => ['ze_ada_class'],
                ],
                [
                    ['an_ada_class', 'an Ada Class'],
                    ['ze_ada_class', 'ze Ada Class'],
                ],
            ],
            'special_characters' => [
                [
                    'A' => ['an_ada_class'],
                    '@' => ['ze_ada_class'],
                ],
                [
                    ['an_ada_class', '@!()&%^£An Ada Class'],
                    ['ze_ada_class', '@!()&%^£'],
                ],
            ],
            'letters_with_diacritic' => [
                [
                    'A' => ['an_ada_class'],
                    'Z' => ['ze_ada_class'],
                ],
                [
                    ['an_ada_class', 'Án Ada Class'],
                    ['ze_ada_class', 'Że Ada Class'],
                ],
            ],
        ];
    }
}
