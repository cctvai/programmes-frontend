<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Domain;

use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;

class ContactPage
{
    /** @var string|null */
    private $ugcCampaignId;

    /** @var ContactDetails[]|null */
    private $contactDetails;

    public function __construct(
        ?string $ugcCampaignId,
        ?array $contactDetails
    ) {
        $this->ugcCampaignId = $ugcCampaignId;
        $this->contactDetails = $contactDetails;
    }

    public function getUgcCampaignId(): ?string
    {
        return $this->ugcCampaignId;
    }

    public function getContactDetails(): ?array
    {
        return $this->contactDetails;
    }
}
