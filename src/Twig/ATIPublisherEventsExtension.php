<?php
declare(strict_types = 1);
namespace App\Twig;

use App\DsShared\Helpers\ATIPublisherEventsHelper;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * example elementIds:
 *      title
 *      cta
 *      of_topic
 *      cta_play_from_start
 *      cta_download
 *      subscribe_in_sounds
 *      subscribe_rss
 *      topic
 *      all_bbc
 *      social-facebook
 *      social-instagram
 */
class ATIPublisherEventsExtension extends AbstractExtension
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * provided an element id and an optional prefix,
     * this function returns data-bbc-container and data-bbc-title attributes
     * only when both container and title are non-falsy.
     * the container is inferred from the top level controller,
     * and prefix:elementId forms the title
     * @param string $elementId
     * @param string $prefix
     * @return array
     */
    public function getATIPublisherAttributes(?string $elementId, ?string $prefix = ''): array
    {
        $app = $this->twig->getGlobals()['app'] ?? false;   // tests
        if (!$app) {
            return [];
        }
        $req = $app->getRequest();
        if (!$req) {
            return [];
        }

        $controllerClass = $req->get('_controller');
        $container = ATIPublisherEventsHelper::getContainer($controllerClass);
        $title = $prefix ? "{$prefix}:{$elementId}" : $elementId;

        // to debug, undo this guard, and uncomment the commented lines further down
        if (!($container && $title)) {
            return [];
        }

        return [
            'data-bbc-container' => $container,
            'data-bbc-title' => $title,
//           'title' => "c: '{$container}' t: '{$title}'",
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_ati_attributes', [$this, 'getATIPublisherAttributes']),
        ];
    }
}
