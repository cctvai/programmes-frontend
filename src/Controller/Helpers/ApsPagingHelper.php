<?php
declare(strict_types=1);

namespace App\Controller\Helpers;

use Symfony\Component\HttpFoundation\Request;

/**
 * ApsPagingHelper returns various parameters from Request
 * e.g. page and limit, in a consistent way across controllers
 * @static
 */
class ApsPagingHelper
{
    protected const DEFAULTS = [
        'page' => 1,
        'pageMin' => 1,
        'pageMax' => 99999,
        'limit' => 30,
        'limitMin' => 1,
        'limitMax' => 100,
    ];

    /**
     * Get the page number (page) and page size (limit) from Request
     * @param Request $req
     * @param array $options see DEFAULTS for format
     * @return int[] page, limit
     */
    public static function getPageAndLimit(Request $req, array $options = []): array
    {
        $opts = $options + static::DEFAULTS;
        $page = static::queryParamToInt($req, 'page', $opts['page'], $opts['pageMin'], $opts['pageMax']);
        $limit = static::queryParamToInt($req, 'limit', $opts['limit'], $opts['limitMin'], $opts['limitMax']);

        // ?limit is only allowed in increments of 10, mostly for caching reasons
        $limit = ceil($limit / 10) * 10;

        return [$page, $limit];
    }

    private static function queryParamToInt(
        Request $request,
        string $param,
        int $default,
        int $min = null,
        int $max = null
    ): int {
        $options = ['default' => $default];

        if (!is_null($min)) {
            $options['min_range'] = $min;
        }

        if (!is_null($max)) {
            $options['max_range'] = $max;
        }

        return (int) $request->query->filter(
            $param,
            null,
            FILTER_VALIDATE_INT,
            [ 'options' => $options ]
        );
    }
}
