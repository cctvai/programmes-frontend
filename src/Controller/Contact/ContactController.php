<?php

declare(strict_types = 1);

namespace App\Controller\Contact;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;

class ContactController extends BaseController
{
    public function __invoke(
        CoreEntity $coreEntity
    ) {
        $this->setContextAndPreloadBranding($coreEntity);

        return $this->renderWithChrome('contact/contact.html.twig', [
            'form' => true,
            'details' => true,
        ]);
    }
}
