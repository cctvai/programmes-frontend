<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter;

use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExternalLinkCtaPresenter extends BaseCtaPresenter
{
    private $url;

    public function __construct(string $url, UrlGeneratorInterface $router, array $options = [])
    {
        parent::__construct(null, $router, $options);
        $this->url = $url;
    }

    public function getMediaIconType(): string
    {
        return 'basics';
    }

    public function getMediaIconName(): string
    {
        return 'external-link';
    }

    public function getLinkLocation(): string
    {
        return '';
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLabelTranslation(): string
    {
        return '';
    }
}
