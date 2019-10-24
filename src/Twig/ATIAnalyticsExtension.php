<?php
declare(strict_types = 1);
namespace App\Twig;

use App\DsShared\Helpers\ATIPublisherEventsHelper;
use App\ValueObject\CosmosInfo;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Controller\Helpers\DestinationVariableHelper;
use App\Controller\Helpers\ProducerVariableHelper;

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
class ATIAnalyticsExtension extends AbstractExtension
{
    private $twig;
    private $cosmosInfo;
    private $prodVarHelper;
    private $destVarHelper;

    public function __construct(Environment $twig, CosmosInfo $cosmosInfo)
    {
        $this->cosmosInfo = $cosmosInfo;
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
        /* @var $app \Symfony\Bridge\Twig\AppVariable */
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

    /**
     * get the Site parameter for ATI event stats
     * @see https://confluence.dev.bbc.co.uk/pages/viewpage.action?pageId=175885338#tab--2022260337
     * @param CoreEntity $coreEntity
     * @return string
     */
    public function getATIDestination($coreEntity)
    {
        if (!$this->destVarHelper) {
            $this->destVarHelper = new DestinationVariableHelper();
        }
        return $this->destVarHelper
            ->getDestinationFromContext($coreEntity, $this->cosmosInfo->getAppEnvironment());
    }

    /**
     * get the L2 parameter for ATI event stats
     * @see https://confluence.dev.bbc.co.uk/pages/viewpage.action?pageId=175885338#tab-79895020
     * @param CoreEntity $coreEntity
     * @return string
     */
    public function getATIProducer(CoreEntity $coreEntity): string
    {
        if (!$this->prodVarHelper) {
            $this->prodVarHelper = new ProducerVariableHelper();
        }
        return $this->prodVarHelper->calculateProducerVariable($coreEntity);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_ati_attributes', [$this, 'getATIPublisherAttributes']),
            new TwigFunction('get_ati_producer', [$this, 'getATIProducer']),
            new TwigFunction('get_ati_destination', [$this, 'getATIDestination']),
        ];
    }
}
