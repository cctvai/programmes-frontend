<?php

declare(strict_types = 1);

namespace App\Controller\Atoz;

use App\Controller\BaseController;

class ShowController extends BaseController
{
    public function __invoke(string $search, string $slice)
    {
        $selectedLetter = '';
        if (strlen($search) === 1) {
            if ($search === '@') {
                $selectedLetter = '0-9';
            } else {
                $selectedLetter = strtolower($search);
            }
        }

        if ($slice === 'all') {
            $descriptionSlice = 'all';
        } else {
            $descriptionSlice = 'available';
        }
        if ($selectedLetter === '') {
            $descriptionSearch = 'matching "' . $search . '"';
        } else {
            $descriptionSearch = 'beginning with ' . strtoupper($selectedLetter);
        }
        $this->overridenDescription = 'A list of '
                                    . $descriptionSlice
                                    . ' BBC television, radio and other programmes '
                                    . $descriptionSearch
                                    . '.';

        return $this->renderWithChrome('atoz/show.html.twig', [
            'selectedLetter' => $selectedLetter,
            'search' => $search,
            'slice' => $slice,
        ]);
    }
}
