<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Faq;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\DsShared\FixIsiteMarkupInterface;
use App\DsShared\Helpers\FixIsiteMarkupTrait;
use App\ExternalApi\Isite\Domain\ContentBlock\Faq;

class FaqPresenter extends ContentBlockPresenter implements FixIsiteMarkupInterface
{
    use FixIsiteMarkupTrait;

    /** @var Faq */
    protected $block;


    public function __construct(Faq $faqBlock, bool $inPrimaryColumn, bool $isPrimaryColumnFullWith, array $options = [])
    {
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
