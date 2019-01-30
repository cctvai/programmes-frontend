<?php

namespace App\Ds2013\Presenters\Section\Contact\Details;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Enumeration\ContactMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;

class ContactDetailsPresenter extends Presenter
{
    /** @var ContactDetails[] */
    private $contactDetails;

    public function __construct(array $contactDetails, array $options = [])
    {
        $this->contactDetails = $contactDetails;

        parent::__construct($options);
    }

    public function getContactDetails(): array
    {
        return $this->contactDetails;
    }

    public function getGeliconFolderByType(string $type): string
    {
        switch ($type) {
            case ContactMediumEnum::EMAIL:
            case ContactMediumEnum::FAX:
            case ContactMediumEnum::SMS:
            case ContactMediumEnum::PHONE:
            case ContactMediumEnum::ADDRESS:
                return 'basics';
            default:
                return 'social';
        }
    }

    public function getGeliconNameByType(string $type): string
    {
        switch ($type) {
            case ContactMediumEnum::FAX:
                return 'print';
            case ContactMediumEnum::SMS:
            case ContactMediumEnum::PHONE:
                return 'mobile';
            case ContactMediumEnum::ADDRESS:
                return 'home';
            case ContactMediumEnum::OTHER:
                return 'comments';
            default:
                return $type;
        }
    }

    public function getTitleByType(string $type): string
    {
        switch ($type) {
            case ContactMediumEnum::GOOGLE_PLUS:
                return 'Google+';
            case 'linkedin':
                return 'LinkedIn';
            case ContactMediumEnum::STUMBLEUPON:
                return 'StumbleUpon';
            default:
                return ucwords($type);
        }
    }

    public function getUrlByContactDetail(ContactDetails $contactDetail): ?string
    {
        switch ($contactDetail->getType()) {
            case ContactMediumEnum::PINTEREST:
            case ContactMediumEnum::SPOTIFY:
            case ContactMediumEnum::STUMBLEUPON: 
            case 'reddit':
            case 'linkedin':
            case 'digg':
            case ContactMediumEnum::GOOGLE_PLUS:
            case ContactMediumEnum::TUMBLR:
            case ContactMediumEnum::FACEBOOK:
            case ContactMediumEnum::INSTAGRAM:
            case ContactMediumEnum::TWITTER:
                return $contactDetail->getValue();
            case ContactMediumEnum::EMAIL:
                return 'mailto:' . $contactDetail->getValue();
            default:
                return null;
        }
    }
}
