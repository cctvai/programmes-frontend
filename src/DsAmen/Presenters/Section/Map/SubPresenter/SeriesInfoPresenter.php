<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\DsShared\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;

class SeriesInfoPresenter extends ProgrammeInfoPresenter
{
    /** @var TitleLogicHelper */
    private $titleLogicHelper;

    private $mainTitleProgramme;

    private $subTitleProgrammes;

    public function __construct(
        TitleLogicHelper $titleLogicHelper,
        ProgrammeContainer $programme,
        array $options = []
    ) {
        parent::__construct($programme, $options);
        $this->titleLogicHelper = $titleLogicHelper;

        list ($this->mainTitleProgramme, $this->subTitleProgrammes)
            = $this->titleLogicHelper->getOrderedProgrammesForTitle($programme, null, 'item::ancestry');
    }

    public function getTemplateVariableName(): string
    {
        return 'programme_info';
    }

    public function getMainTitleProgramme(): ProgrammeContainer
    {
        return $this->mainTitleProgramme;
    }

    public function getSubTitleProgrammes(): array
    {
        return $this->subTitleProgrammes;
    }
}
