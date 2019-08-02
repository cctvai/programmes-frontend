<?php
declare(strict_types = 1);
namespace Tests\App;

use App\DsShared\BasePresenter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class BaseTemplateTestCase extends WebTestCase
{
    /** @var Environment */
    protected static $twig;

    /** @var \App\Ds2013\Factory\PresenterFactory */
    protected static $ds2013PresenterFactory;

    /** @var \App\DsAmen\Factory\PresenterFactory */
    protected static $dsAmenPresenterFactory;

    /** @var \App\DsShared\Factory\PresenterFactory */
    protected static $dsSharedPresenterFactory;

    /** @var RouterInterface */
    protected static $router;

    protected static $translator;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public static function setUpBeforeClass()
    {
        if (self::$twig !== null) {
            return;
        }
        self::bootKernel();

        // returns the real and unchanged service container
        $container = self::$kernel->getContainer();
        // gets the special container that allows fetching private services
        $container = self::$container;
        self::$twig = $container->get('twig');
        self::$ds2013PresenterFactory = $container->get(\App\Ds2013\Factory\PresenterFactory::class);
        self::$dsAmenPresenterFactory = $container->get(\App\DsAmen\Factory\PresenterFactory::class);
        self::$dsSharedPresenterFactory = $container->get(\App\DsShared\Factory\PresenterFactory::class);
        self::$router = $container->get(RouterInterface::class);
        self::$translator = $container->get(TranslatorInterface::class);
    }

    protected function presenterHtml(BasePresenter $presenter): string
    {
        return self::$twig->load($presenter->getTemplatePath())->render([
            $presenter->getTemplateVariableName() => $presenter,
        ]);
    }

    /**
     * Get a Dom Crawler populated with the output of a template.
     *
     * @return Crawler The Dom Crawler populated with the twig template
     *
     * @throws Twig_Error_Loader  When the template cannot be found
     * @throws Twig_Error_Syntax  When an error occurred during compilation
     * @throws Twig_Error_Runtime When an error occurred during rendering
     */
    protected function presenterCrawler(BasePresenter $presenter): Crawler
    {
        return new Crawler($this->presenterHtml($presenter));
    }

    protected function assertHasClasses(string $expectedClasses, Crawler $node, $message): void
    {
        $expectedClassesArray = explode(' ', $expectedClasses);
        $classesArray = explode(' ', $node->attr('class'));
        // Check that all classes in $classes are present in
        // the class attribute of the node. Extra classes are ok.
        $hasClasses = !array_diff($expectedClassesArray, $classesArray);
        $this->assertTrue($hasClasses, $message);
    }
}
