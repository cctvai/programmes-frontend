<?php

namespace App\ExternalApi\Isite\Domain\ContentBlock;

class Telescope extends AbstractContentBlock
{
    /** @var string */
    private $voteId;

    /** @var bool */
    private $isUkOnly;

    /** @var string */
    private $name;

    public function __construct(
        ?string $title,
        string $voteId,
        ?string $isUkOnly,
        string $name
    ) {
        parent::__construct($title);

        $this->voteId = $voteId;
        $this->isUkOnly = ($isUkOnly === 'Yes');
        $this->name = $name;
    }

    public function getVoteId(): string
    {
        return $this->voteId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isUkOnly(): bool
    {
        return $this->isUkOnly;
    }

    public function getType(): string
    {
        return 'telescope';
    }
}
