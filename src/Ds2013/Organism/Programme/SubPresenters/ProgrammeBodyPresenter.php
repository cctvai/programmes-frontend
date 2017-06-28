<?php
declare(strict_types = 1);

namespace App\Ds2013\Organism\Programme\SubPresenters;

use App\Ds2013\Helpers\PlayTranslationsHelper;
use App\Ds2013\Organism\Programme\ProgrammePresenterBase;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use InvalidArgumentException;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ProgrammeImagePresenter
 *
 * Sub-presenter for ProgrammePresenter
 *
 * @package App\Ds2013\Organism\Programme\ProgrammeImage
 */
class ProgrammeBodyPresenter extends ProgrammePresenterBase
{
    /** @var array */
    protected $options = [
        'show_synopsis' => true,
        'show_duration' => false,
    ];

    /** @var PlayTranslationsHelper */
    protected $playTranslationsHelper;

    public function __construct(
        UrlGeneratorInterface $router,
        PlayTranslationsHelper $playTranslationsHelper,
        Programme $programme,
        array $options = []
    ) {
        // Clips show duration by default
        if ($programme instanceof Clip) {
            $this->options['show_duration'] = true;
        }

        parent::__construct($router, $programme, $options);
        $this->playTranslationsHelper = $playTranslationsHelper;
    }

    public function getDurationInWords(): string
    {
        if (!$this->programme instanceof ProgrammeItem) {
            throw new InvalidArgumentException("Cannot get duration for non-programmeitem");
        }
        return $this->playTranslationsHelper->secondsToWords($this->programme->getDuration());
    }

    public function getSynopsisTruncationLength()
    {
        if ($this->options['truncation_length']) {
            return $this->options['truncation_length'] * 1.5;
        }
        return null;
    }

    public function hasDefinedPositionUnderParentProgramme(): bool
    {
        $parent = $this->programme->getParent();
        if ($parent instanceof ProgrammeContainer
            && !is_null($parent->getExpectedChildCount())
            && !is_null($this->programme->getPosition())
        ) {
            return true;
        }
        return false;
    }

    public function shouldShowDuration(): bool
    {
        return (
            $this->options['show_duration'] &&
            $this->programme instanceof ProgrammeItem &&
            $this->programme->getDuration()
        );
    }
}
