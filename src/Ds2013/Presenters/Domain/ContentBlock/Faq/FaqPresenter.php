<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Faq;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\DsShared\Helpers\FixIsiteMarkupHelper;
use App\ExternalApi\Isite\Domain\ContentBlock\Faq;

class FaqPresenter extends ContentBlockPresenter
{
    /** @var Faq */
    protected $block;

    /** @var FixIsiteMarkupHelper */
    private $fixIsiteMarkupHelper;

    public function __construct(Faq $faqBlock, bool $inPrimaryColumn, bool $isPrimaryColumnFullWith, FixIsiteMarkupHelper $fixMarkupHelper, array $options = [])
    {
        $this->fixIsiteMarkupHelper = $fixMarkupHelper;
        parent::__construct($faqBlock, $inPrimaryColumn, $isPrimaryColumnFullWith, $options);
    }

    /**
     * Get the questions and cleaned answers (as in V2)
     *
     * @return array
     */
    public function getQuestions(): array
    {
        $questions = [];
        foreach ($this->block->getQuestions() as $question) {
            $cleanedAnswer = implode('', $this->fixIsiteMarkupHelper->getParagraphs($question['answer']));
            $questions[] = [
                'question' => $question['question'],
                'answer' => $cleanedAnswer,
            ];
        }
        return $questions;
    }
}
