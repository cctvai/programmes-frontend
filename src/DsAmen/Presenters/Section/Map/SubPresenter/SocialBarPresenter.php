<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Enumeration\ContactMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;
use BBC\ProgrammesPagesService\Domain\ValueObject\UGCContactDetails;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocialBarPresenter extends Presenter
{
    /** @var ContactDetails[] */
    private $socialMediaDetails;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        UrlGeneratorInterface $router,
        RequestStack $requestStack,
        Programme $programme,
        array $options = []
    ) {
        parent::__construct($options);
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->socialMediaDetails = $this->filterSocialMediaDetails($programme->getOption('contact_details') ?? []);
    }

    /** @return ContactDetails[] */
    public function getSocialMediaDetails(): array
    {
        return $this->socialMediaDetails;
    }

    public function getUGCHref(UGCContactDetails $UGCContactDetail)
    {
        return $this->router->generate('ugc_form', ['campaignId' => $UGCContactDetail->getValue()])
            . '?ptrt=' . urlencode($this->requestStack->getCurrentRequest()->getUri());
    }

    /**
     * @param ContactDetails[] $details
     * @return ContactDetails[]
     */
    private function filterSocialMediaDetails(array $details): array
    {
        $details = array_filter($details, function (ContactDetails $details) {
            return !in_array($details->getType(), [
                ContactMediumEnum::EMAIL,
                ContactMediumEnum::SMS,
                ContactMediumEnum::PHONE,
                ContactMediumEnum::FAX,
                ContactMediumEnum::ADDRESS,
                ContactMediumEnum::OTHER,
            ]);
        });

        return array_slice($details, 0, 5);
    }
}
