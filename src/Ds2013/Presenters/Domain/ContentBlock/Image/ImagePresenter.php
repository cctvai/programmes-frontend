<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Image;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Image;

class ImagePresenter extends ContentBlockPresenter
{
    public function __construct(Image $imageBlock, bool $inPrimaryColumn, bool $isPrimaryColumnFullWith, array $options = [])
    {
        parent::__construct($imageBlock, $inPrimaryColumn, $isPrimaryColumnFullWith, $options);
    }

    /**
     * Get the image slot used for responsive images
     * https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
     * Gel breakpoints defined under _variables.scss
     *
     * @return string Return the right slot size for Gel4
     */
    public function getGel4ImageSlot():string
    {
        if ($this->isPrimaryColumnFullWith()) {
            return '944px';
        }
        if ($this->isInPrimaryColumn()) {
            return '530px'; // max size it can reach
        }
        // secondary column
        return '397px'; // max size it can reach
    }

    /**
     * Get the image slot used for responsive images
     * https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
     * Gel breakpoints defined under _variables.scss
     *
     * @return float Return the right slot size for Gel3b
     */
    public function getGel3bImageSlot():float
    {
        if ($this->isPrimaryColumnFullWith()) {
            return 1;
        }
        return 1/2;
    }
}
