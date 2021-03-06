<?php
declare(strict_types = 1);
namespace App\Twig;

use RMP\Comscore\Comscore;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides tools for rendering adverts.
 *
 * Within a page template, call `adverts_set_blocks` to define the advert slots
 * that should be rendered within the page. Then add a call to `adverts_head`
 * within the <head> of the page (this will probably be done in a shared base
 * template). Finally call `advert` to render an advert of a given type and
 * group.
 */
class BbcDotComExtension extends AbstractExtension
{
     /** @var string */
    private $advertBlocks = '';

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('advert', [$this, 'advert'], ['is_safe' => ['html']]),
            new TwigFunction('adverts_set_blocks', [$this, 'advertsSetBlocks']),
            new TwigFunction('bbc_dotcom_head', [$this, 'bbcDotComHead'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Displays an Advert, with a particular ID (e.g. "leaderboard"), at
     * particular group sizes (e.g. [1,2,3,4]), optionally wrapped in a <div>
     * with a given class name
     *
     * @param string $advertId The advert to display e.g. "leaderboard" or "mpu"
     * @param int[] $groups The sizing groups that the advert should be displayed within
     * @param string $containerClass A class that can be used for a wrapping div if desired
     */
    public function advert(
        string $advertId,
        array $groups,
        string $containerClass = ''
    ): string {
        if (!$this->advertBlocks) {
            return '';
        }

        $advertGroups = implode(',', $groups);
        $divId = 'bbccom_' . $advertId . '_' . implode('_', $groups);

        $template = <<<ADVERT
<div class="bbccom_slot" id="%s"><div class="bbccom_advert"><script>
if (window.bbcdotcom && window.bbcdotcom.slotAsync) {
    window.bbcdotcom.slotAsync("%s", [%s]); }
</script></div></div>
ADVERT;

        $ad = sprintf($template, $divId, $advertId, $advertGroups);

        if ($containerClass) {
            $ad = '<div class="' . $containerClass . '">' . $ad . '</div>';
        }

        return $ad;
    }

    /**
     * Defines the set of advert blocks that shall be displayed on the page
     *
     * @param string $advertBlocks A space-separated list of adverts blocks that should be displayed
     */
    public function advertsSetBlocks(string $advertBlocks): bool
    {
        $this->advertBlocks = $advertBlocks;

        return !!$this->advertBlocks;
    }

    public function bbcDotComHead(?Comscore $comscore): string
    {
        if ($this->advertBlocks) {
            return $this->advertsHead();
        }
        if ($comscore) {
            return $this->comscoreHead();
        }
        return '';
    }
    private function advertsHead(): string
    {
        $template = <<<HEAD
<script>
if (window.bbcdotcom) {
    window.bbcdotcom.init({ comScoreEnabled: false, asyncEnabled: true, adsToDisplay: '%s'.split(' ') });
    if (typeof window.bbcdotcom.addLoadEvent !== 'undefined' &&
        typeof window.bbcdotcom.utils !== 'undefined' &&
        typeof window.bbcdotcom.utils.debounce !== 'undefined' &&
        typeof window.bbcdotcom.reset !== 'undefined') {
        window.bbcdotcom.addLoadEvent(function(){
            window.addEventListener('resize', window.bbcdotcom.utils.debounce(window.bbcdotcom.reset, 500));
        });
    }
}
</script>
HEAD;

        return sprintf($template, $this->advertBlocks);
    }

    private function comscoreHead(): string
    {
        // Make sure bbcdotcom comscore double tracking is disabled on pages
        // where comscore is put in by us
        return <<<HEAD
<script>
if (window.bbcdotcom) {
    window.bbcdotcom.init({comScoreEnabled: false, adsEnabled: false});
}
</script>
HEAD;
    }
}
