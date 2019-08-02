<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast\SubPresenter;

use App\DsAmen\Presenter;
use App\DsShared\Helpers\BroadcastNetworksHelper;
use App\DsShared\Helpers\LocalisedDaysAndMonthsHelper;
use App\ValueObject\BroadcastNetworkBreakdown;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use Cake\Chronos\Chronos;
use Symfony\Component\Translation\TranslatorInterface;

class DetailsPresenter extends Presenter
{
    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    private $translator;

    /** @var LocalisedDaysAndMonthsHelper */
    public $localisedDaysAndMonthsHelper;

    /** @var BroadcastNetworkBreakdown[] */
    private $networksAndServicesDetails;

    public function __construct(
        CollapsedBroadcast $collapsedBroadcast,
        TranslatorInterface $translator,
        LocalisedDaysAndMonthsHelper $localisedDaysAndMonthsHelper,
        BroadcastNetworksHelper $broadcastNetworksHelper,
        array $options = []
    ) {
        parent::__construct($options);
        $this->localisedDaysAndMonthsHelper = $localisedDaysAndMonthsHelper;
        $this->collapsedBroadcast = $collapsedBroadcast;
        $this->translator = $translator;
        $this->networksAndServicesDetails = $broadcastNetworksHelper->getNetworksAndServicesDetails($collapsedBroadcast);
    }

    public function getDateTimestamp(): string
    {
        return $this->collapsedBroadcast->getStartAt()->toAtomString();
    }

    public function getDay(): string
    {
        return $this->localisedDaysAndMonthsHelper->getFormatedDay($this->collapsedBroadcast->getStartAt());
    }

    public function getNetworksAndServicesDetails(): array
    {
        return $this->networksAndServicesDetails;
    }

    public function getTime(): Chronos
    {
        return $this->collapsedBroadcast->getStartAt();
    }
}
