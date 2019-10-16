<?php
declare(strict_types = 1);
namespace App\Branding;

use BBC\BrandingClient\Branding;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\ValueObject\UGCContactDetails;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Consider "I'm Sorry I Haven't a Clue" - it is a Radio 4 programme. Radio
 * programmes tend not to have bespoke per-brand theming, instead they inherit
 * their theme from their Service (in this case Radio 4). This however poses
 * a small problem - if we display the Service theme, how shall we show the
 * correct per-programme Title and Navigation?
 *
 * This is solved by the Branding Tool outputting templates that contain
 * placeholder sections such as <!--BRANDING_PLACEHOLDER_TITLE--> and
 * <!--BRANDING_PLACEHOLDER_NAV--> when there is no programme set.
 *
 * This takes a Branding instance that contains those placeholders and a
 * "context" and replaces those placeholders with a title and default
 * navigation, based up the contents of the context.
 */
class BrandingPlaceholderResolver
{
    private const PLACEHOLDER_TITLE = '<!--BRANDING_PLACEHOLDER_TITLE-->';
    private const PLACEHOLDER_NAV = '<!--BRANDING_PLACEHOLDER_NAV-->';
    private const PLACEHOLDER_NAV_END = '<!--BRANDING_PLACEHOLDER_NAV_END-->';

    /** @var UrlGeneratorInterface */
    private $router;
    private $translator;
    private $requestStack;

    public function __construct(
        UrlGeneratorInterface $router,
        TranslatorInterface $translator,
        RequestStack $requestStack
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    public function resolve(Branding $branding, $context): Branding
    {
        // If the context is not a Programme or Group then don't attempt to resolve
        if (!($context instanceof CoreEntity)) {
            return $branding;
        }

        // Currently placeholders only exist in the bodyFirst section
        return new Branding(
            $branding->getHead(),
            $this->resolvePlaceholders($branding, $context, $branding->getBodyFirst()),
            $branding->getBodyLast(),
            $branding->getColours(),
            $branding->getOptions()
        );
    }

    private function resolvePlaceholders(Branding $branding, $context, string $html)
    {
        // Check if the placeholder is present in the haystack, before
        // attempting the replace to avoid doing extra work building the
        // replacement text, if there is nothing to replace.

        // Title
        if (strpos($html, self::PLACEHOLDER_TITLE) !== false) {
            $html = str_replace(
                self::PLACEHOLDER_TITLE,
                $this->buildTitle($context),
                $html
            );
        }

        // Navigation
        if (strpos($html, self::PLACEHOLDER_NAV) !== false) {
            $html = str_replace(
                self::PLACEHOLDER_NAV,
                $this->buildNav($context, $branding),
                $html
            );
        }

        // Add UGC contact link to top nav (controlled by TLEO options)
        $tleo = $context->getTleo();
        $contactDetails = $tleo->getOption('contact_details');
        if ($contactDetails && strpos($html, self::PLACEHOLDER_NAV_END) !== false) {
            $UGCContactDetail = null;
            foreach ($contactDetails as $contactDetail) {
                if ($contactDetail instanceof UGCContactDetails) {
                    $UGCContactDetail = $contactDetail;
                }
            }
            if ($UGCContactDetail && $UGCContactDetail->isInTopNav()) {
                $contactTitle = $UGCContactDetail->getTitle() ?? $this->translator->trans('contact_form');
                $contactHref = $this->router->generate('ugc_form', [
                    'campaignId' => $UGCContactDetail->getValue(),
                ]) . '?ptrt=' . urlencode($this->requestStack->getCurrentRequest()->getUri());
                $contactLink = "<li class=\"br-nav__item\"><a class=\"br-nav__link\" href=\"{$contactHref}\">{$contactTitle}</a></li>";
                $html = str_replace(
                    self::PLACEHOLDER_NAV_END,
                    $contactLink,
                    $html
                );
            }
        }

        return $html;
    }

    private function buildTitle($context): string
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $this->router->generate('find_by_pid', ['pid' => $context->getTleo()->getPid()]),
            $context->getTleo()->getTitle()
        );
    }

    private function buildNav($context, Branding $branding): string
    {
        // We've already asserted that $context is a Programme or a Group
        $tleo = $context->getTleo();
        $navItems = [];

        $hasEpisodes = false;
        $hasClips = false;
        $hasGalleries = false;

        // Home link is always present
        $navItems[] = $branding->buildNavItem(
            $this->translator->trans('home'),
            $this->router->generate('find_by_pid', ['pid' => $tleo->getPid()])
        );

        if ($tleo instanceof ProgrammeContainer) {
            $hasEpisodes = $tleo->getAggregatedEpisodesCount() > 0;
        }

        if ($tleo instanceof ProgrammeContainer || $tleo instanceof Episode) {
            $hasClips = $tleo->getAvailableClipsCount() > 0;
            $hasGalleries = $tleo->getAggregatedGalleriesCount() > 0;
        }

        // Episodes link
        if ($tleo && $hasEpisodes) {
            $navItems[] = $branding->buildNavItem(
                $this->translator->trans('episodes'),
                $this->router->generate('programme_episodes', ['pid' => $tleo->getPid()])
            );
        }

        // Clips link
        if ($tleo && $hasClips) {
            $navItems[] = $branding->buildNavItem(
                $this->translator->trans('clips'),
                $this->router->generate('programme_clips', ['pid' => $tleo->getPid()])
            );
        }

        // Galleries link
        if ($tleo && $hasGalleries) {
            $navItems[] = $branding->buildNavItem(
                $this->translator->trans('galleries'),
                $this->router->generate('programme_galleries', ['pid' => $tleo->getPid()])
            );
        }

        return implode('', $navItems);
    }
}
