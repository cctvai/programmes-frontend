<?php
declare (strict_types=1);

namespace App\DsShared\Helpers;

use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\Exception\DataNotFetchedException;

class StreamableHelper
{
    public function getRouteForProgrammeItem(ProgrammeItem $programmeItem): string
    {
        if ($this->shouldStreamViaIplayer($programmeItem)) {
            return 'iplayer_play';
        }

        if ($this->shouldStreamViaPlayspace($programmeItem)) {
            return 'playspace_play';
        }

        return 'find_by_pid';
    }

    public function shouldTreatProgrammeItemAsAudio(ProgrammeItem $programmeItem): bool
    {
        if ($programmeItem->isAudio()) {
            return true;
        }
        if ($programmeItem->getMediaType() == MediaTypeEnum::UNKNOWN) {
            $network = $programmeItem->getNetwork();
            if (!is_null($network) && $network->isRadio()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Before calling this function you should to verify $programmeItem->hasPlayableDestination() is true
     *
     * @param ProgrammeItem $programmeItem
     * @return bool
     */
    public function shouldStreamViaIplayer(ProgrammeItem $programmeItem): bool
    {
        return !($programmeItem instanceof Clip || !$programmeItem->isVideo());
    }

    /**
     * Before calling this function you should to verify $programmeItem->hasPlayableDestination() is true
     *
     * @param ProgrammeItem $programmeItem
     * @return bool
     * @throws DataNotFetchedException
     */
    public function shouldStreamViaPlayspace(ProgrammeItem $programmeItem): bool
    {
        $isPlayspaceMasterBrand = ($programmeItem->getMasterBrand() && $programmeItem->getMasterBrand()->isStreamableInPlayspace());
        if ($isPlayspaceMasterBrand && $programmeItem->isAudio()) {
            return true;
        }
        return false;
    }
}
