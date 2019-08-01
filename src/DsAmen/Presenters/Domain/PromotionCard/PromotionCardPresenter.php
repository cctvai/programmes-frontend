<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\PromotionCard;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use Symfony\Component\Routing\RouterInterface;

class PromotionCardPresenter extends Presenter
{
    /** @var Promotion */
    private $promotion;

    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router, Promotion $promotion, array $options = [])
    {
        parent::__construct($options);
        $this->promotion = $promotion;
        $this->router = $router;
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
        $promotedEntity = $this->promotion->getPromotedEntity();

        if ($promotedEntity instanceof Image) {
            return $this->promotion->getUrl();
        }

        if ($promotedEntity instanceof CoreEntity) {
            return $this->router->generate('find_by_pid', ['pid' => $promotedEntity->getPid()]);
        }

        return '';
    }
}
