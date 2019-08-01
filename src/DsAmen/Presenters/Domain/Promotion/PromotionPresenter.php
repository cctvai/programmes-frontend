<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\Promotion;

use App\DsAmen\Presenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter\ExternalLinkCtaPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Shared\SubPresenter\StreamableCtaPresenter;
use App\DsShared\Factory\HelperFactory;
use App\DsShared\Helpers\StreamableHelper;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\PromotableInterface;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PromotionPresenter extends Presenter
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var Promotion */
    private $promotion;

    /** @var PromotableInterface  */
    private $promotedEntity;

    /** @var StreamableHelper */
    private $streamableHelper;

    /** @var array */
    protected $options = [
        // display options
        'show_image' => true,
        'show_synopsis' => true,
        'synopsis_size' => 'gel-brevier',
        'related_links_count' => 999,

        // classes & elements
        'h_tag' => 'h4',
        'title_size' => 'gel-pica-bold',
        'img_default_width' => 320,
        'img_sizes' => [],
        'img_is_lazy_loaded' => true,
        'media_variant' => 'media--column media--card',
        'cta_class' => 'cta cta--dark',
        'media_panel_class' => '1/1',
        'branding_name' => 'subtle',
        'link_location_prefix' => 'promotionobject_',
    ];

    public function __construct(
        UrlGeneratorInterface $router,
        Promotion $promotion,
        HelperFactory $helperFactory,
        array $options = []
    ) {
        parent::__construct($options);
        $this->router = $router;
        $this->promotion = $promotion;
        $this->promotedEntity = $this->promotion->getPromotedEntity();
        $this->streamableHelper = $helperFactory->getStreamUrlHelper();
    }

    public function shouldDisplayCta(): bool
    {
        if ($this->promotedEntity instanceof ProgrammeItem && $this->promotedEntity->hasPlayableDestination()) {
            return true;
        }
        if (!empty($this->promotion->getUrl()) && $this->isExternalLink($this->promotion->getUrl())) {
            return true;
        }
        return false;
    }

    public function getCtaPresenter(array $options = [])
    {
        if ($this->promotedEntity instanceof ProgrammeItem && $this->promotedEntity->hasPlayableDestination()) {
            return new StreamableCtaPresenter(
                $this->streamableHelper,
                $this->promotedEntity,
                $this->router,
                $options
            );
        }
        if (!empty($this->promotion->getUrl()) && $this->isExternalLink($this->promotion->getUrl())) {
            return new ExternalLinkCtaPresenter(
                $this->promotion->getUrl(),
                $this->router,
                $options
            );
        }
        return null;
    }

    public function getTitle(): string
    {
        return $this->promotion->getTitle();
    }

    public function getUrl(): string
    {
        if ($this->promotedEntity instanceof Image) {
            return $this->promotion->getUrl();
        }

        // for Episodes only the CTA should link to Playspace but for Clips
        // the whole promotion box should link to Playspace
        if ($this->promotedEntity instanceof Clip && $this->promotedEntity->hasPlayableDestination()) {
            return $this->router->generate(
                $this->streamableHelper->getRouteForProgrammeItem($this->promotedEntity),
                ['pid' => $this->promotedEntity->getPid()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        if ($this->promotedEntity instanceof CoreEntity) {
            return $this->router->generate('find_by_pid', ['pid' => $this->promotedEntity->getPid()]);
        }

        return '';
    }

    public function getImage(): ?Image
    {
        if (!$this->options['show_image']) {
            return null;
        }

        $promotedEntity = $this->promotion->getPromotedEntity();
        if ($promotedEntity instanceof Image) {
            return $promotedEntity;
        }

        if ($promotedEntity instanceof CoreEntity) {
            return $promotedEntity->getImage();
        }

        return null;
    }

    public function getSynopsis(): string
    {
        if (!$this->options['show_synopsis']) {
            return '';
        }

        return $this->promotion->getShortSynopsis();
    }

    /**
     * @return RelatedLink[]
     */
    public function getRelatedLinks(): array
    {
        return array_slice($this->promotion->getRelatedLinks(), 0, $this->options['related_links_count']);
    }

    public function getBrandingBoxClass(): string
    {
        if (empty($this->options['branding_name'])) {
            return '';
        }

        return 'br-box-' . $this->options['branding_name'];
    }

    public function getTextBrandingClass(): string
    {
        if (empty($this->options['branding_name'])) {
            return '';
        }

        return 'br-' . $this->options['branding_name'] . '-text-ontext';
    }

    public function isExternalLink($url): bool
    {
        return (bool) preg_match('~^(https?:)?//(?![^/]*bbc\.co(m|\.uk))~', $url);
    }

    public function getType(): string
    {
        $promotion = $this->promotion;

        if ($promotion->isSuperPromotion()) {
            $prefix = 'super-promotion-';
        } else {
            $prefix = 'promotion-';
        }

        $promotedEntity = $promotion->getPromotedEntity();
        if ($promotedEntity instanceof CoreEntity) {
            return $prefix . $promotedEntity->getType();
        }

        if ($this->isExternalLink($this->getUrl())) {
            return $prefix . 'external-link';
        }

        return $prefix . 'internal-link';
    }

    protected function validateOptions(array $options): void
    {
        if (!is_bool($options['show_image'])) {
            throw new InvalidOptionException('show_image option must be a boolean');
        }

        if (!is_bool($options['show_synopsis'])) {
            throw new InvalidOptionException('show_synopsis option must be a boolean');
        }

        if (!is_int($options['related_links_count']) || $options['related_links_count'] < 0) {
            throw new InvalidOptionException('related_links_count option must 0 or a positive integer');
        }
    }
}
