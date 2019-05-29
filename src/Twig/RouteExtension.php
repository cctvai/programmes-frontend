<?php
declare(strict_types = 1);
namespace App\Twig;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouteExtension extends AbstractExtension
{
    /** @var RouteCollection */
    private $routeCollection;

    public function __construct(RouterInterface $routerInterface)
    {
        $this->routeCollection = $routerInterface->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [new TwigFunction('encoded_path', [$this, 'encodedPath'])];
    }

    public function encodedPath(string $routeName, array $parameters): string
    {
        $route = $this->routeCollection->get($routeName);
        $parameters = array_merge($route->getDefaults(), $parameters);
        $path = $route->getPath();
        foreach ($parameters as $key => $value) {
            $path = str_replace('{' . $key . '}', urlencode((string) $value), $path);
        }
        if ($path[strlen($path) - 1] === '/') {
            $path = substr($path, 0, -1);
        }
        return $path;
    }
}
