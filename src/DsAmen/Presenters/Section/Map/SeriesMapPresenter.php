<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Section\Map;

use App\DsAmen\Presenters\Section\Map\SubPresenter\ProgrammeInfoPresenter;
use App\DsAmen\Presenters\Section\Map\SubPresenter\SeriesInfoPresenter;
use App\DsAmen\Presenters\Section\Map\SubPresenter\SocialBarPresenter;

class SeriesMapPresenter extends MapPresenter
{
    const TEMPLATE_PATH_CLASS_OVERRIDE = MapPresenter::class;

    public function getSocialBarPresenter(): ?SocialBarPresenter
    {
        return null;
    }

    protected function constructLeftColumn(): void
    {
        $leftColumnOptions = [
            'is_three_column' => $this->countTotalColumns() === 3,
            'show_mini_map' => $this->showMiniMap,
        ];
        $this->leftColumn = new SeriesInfoPresenter(
            $this->helperFactory->getTitleLogicHelper(),
            $this->programme,
            $leftColumnOptions
        );
    }
}
