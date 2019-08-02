<?php
declare(strict_types = 1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StreamClipsExtension extends AbstractExtension
{
    private $streams = [];

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('add_stream', [$this, 'addStream']),
            new TwigFunction('get_streams', [$this, 'getStreams']),
        ];
    }

    public function addStream(int $idStream)
    {
        $this->streams[] = $idStream;
    }

    /**
     * @return int[]
     */
    public function getStreams(): array
    {
        return $this->streams;
    }
}
