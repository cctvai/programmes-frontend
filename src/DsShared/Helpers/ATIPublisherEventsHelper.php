<?php
declare(strict_types = 1);

namespace App\DsShared\Helpers;

use App\Controller\FindByPid;
use App\Controller\Topic;
use App\Controller\Podcast;
use App\Controller\Clips;

class ATIPublisherEventsHelper
{
    private const BRAND = 'brand';
    private const EPISODE = 'episode';
    private const TOPIC = 'topic';
    private const PODCAST = 'podcasts';
    private const CLIP = 'clip';
    private const GALLERY = 'gallery';

    private const CONTAINER_MAP = [
        // controller class => context
        FindByPid\TlecController::class => self::BRAND,
        FindByPid\SeriesController::class => self::BRAND,
        FindByPid\EpisodeController::class => self::EPISODE,
        Topic\ListController::class => self::TOPIC,
        Topic\ShowController::class => self::TOPIC,
        Podcast\PodcastController::class => self::PODCAST,
        FindByPid\ClipController::class => self::CLIP,
        Clips\ListController::class => self::CLIP,
        FindByPid\GalleryController::class => self::GALLERY,
    ];

    public static function getContainer($controllerClass)
    {
        return self::CONTAINER_MAP[$controllerClass] ?? '';
    }
}
