<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\TopicList;

use App\DsAmen\Presenter;
use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Normalizer;

class TopicListPresenter extends Presenter
{
    /** @var AdaClass[] */
    private $topics;

    /** @var array */
    private $topicsMap;

    /** @var ProgrammeContainer|null */
    private $programmeContainer;

    /** @var array */
    protected $options = [
        'list_tag' => 'ul',
        'show_count' => true,
        'show_letter_headings' => false,
    ];

    public function __construct(
        array $topics,
        ?ProgrammeContainer $programmeContainer,
        array $options = []
    ) {
        parent::__construct($options);
        $this->mapTopics($topics);
        $this->programmeContainer = $programmeContainer;
    }

    public function getTopics(): array
    {
        if ($this->getOption('show_letter_headings')) {
            return $this->topicsMap;
        }

        return $this->topics;
    }

    public function getProgrammeContainer(): ?ProgrammeContainer
    {
        return $this->programmeContainer;
    }

    private function mapTopics(array $topics)
    {
        if ($this->getOption('show_letter_headings')) {
            $topicsMap = [];
            foreach ($topics as $topic) {
                $firstLetter = '';
                $topicTitle = Normalizer::normalize($topic->getTitle(), Normalizer::FORM_D);
                for ($i = 0, $l = strlen($topicTitle); $i < $l; $i++) {
                    $letter = $topicTitle[$i];
                    if (ctype_alpha($letter)) {
                        $firstLetter = strtoupper($letter);
                        break;
                    } else if (is_numeric($letter)) {
                        $firstLetter = '0-9';
                        break;
                    }
                }
                if ($firstLetter === '') {
                    $firstLetter = $topicTitle[0];
                }
                $topicsMap[$firstLetter][] = $topic;
            }
            $this->topicsMap = $topicsMap;
        } else {
            $this->topics = $topics;
        }
    }
}
