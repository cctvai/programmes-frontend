<?php

declare(strict_types=1);

namespace App\ExternalApi\Isite\Domain\ContentBlock;

use BBC\ProgrammesMorphLibrary\Entity\MorphView;

class Riddle extends AbstractContentBlock
{
    /** @var string */
    private $name;

    /** @var string */
    private $riddleId;

    /** @var MorphView */
    private $content;

    public function __construct(
        ?string $title,
        ?string $name,
        string $riddleId,
        MorphView $content
    ) {
        parent::__construct($title);

        $this->name = $name;
        $this->riddleId = $riddleId;
        $this->content = $content;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRiddleId(): string
    {
        return $this->riddleId;
    }

    public function getBody(): string
    {
        return $this->content->getBody();
    }

    public function getHead(): array
    {
        return $this->content->getHead();
    }

    public function getType(): string
    {
        return 'riddle';
    }
}
