<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\PromotionCard;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\Image;

class PromotionCardPresenter extends Presenter
{
    /** @var Promotion */
    private $promotion;

    public function __construct(Promotion $promotion, array $options = [])
    {
        parent::__construct($options);
        $this->promotion = $promotion;
    }

    public function getTitle(): string
    {
        return $this->promotion->getTitle();
    }

    public function getSynopsis(): string
    {
        return $this->promotion->getShortSynopsis();
    }

    public function getImageUrl(): string
    {
        /** @var Image|CoreEntity */
        $promotedEntity = $this->promotion->getPromotedEntity();

        if ($promotedEntity instanceof Image) {
            return $promotedEntity->getUrl(640, 360);
        }

        return $promotedEntity->getImage()->getUrl(640, 360);
    }

    public function getRelatedLinks(): array
    {
        return $this->promotion->getRelatedLinks();
    }

    public function getUrl(): string
    {
        return $this->promotion->getUrl();
    }
}
