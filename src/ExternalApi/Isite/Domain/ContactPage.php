<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Domain;

use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;

class ContactPage
{
    /** @var string|null */
    private $ugcCampaignId;

    public function __construct(
        ?string $ugcCampaignId
    ) {
        $this->ugcCampaignId = $ugcCampaignId;
    }

    public function getUgcCampaignId(): ?string
    {
        return $this->ugcCampaignId;
    }
}
