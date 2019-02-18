<?php

declare(strict_types = 1);

namespace App\Controller\Atoz;

use App\Controller\BaseController;

class ShowController extends BaseController
{
    public function __invoke(string $search, string $slice)
    {
        $current = '';
        if (strlen($search) === 1) {
            if ($search === '@') {
                $current = '0-9';
            } else {
                $current = strtolower($search);
            }
        }

        // TODO: add meta robots noindex,nofollow for keyword searches

        return $this->renderWithChrome('atoz/show.html.twig', [
            'current' => $current,
            'search' => $search,
            'slice' => $slice,
        ]);
    }
}
