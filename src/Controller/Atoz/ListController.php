<?php

declare(strict_types = 1);

namespace App\Controller\Atoz;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListController extends BaseController
{
    public function __invoke(Request $request, string $slice)
    {
        $this->setAtiContentLabels('list-atoz', 'atoz-episodes');

        $searchQuery = preg_replace('/[^A-Za-z0-9 \'-]/', '', $request->query->get('query', ''));
        if ($searchQuery !== '') {
            return $this->cachedRedirectToRoute('atoz_show', [
                'search' => $searchQuery,
                'slice' => $slice,
            ]);
        }

        return $this->cachedRedirectToRoute('atoz_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
