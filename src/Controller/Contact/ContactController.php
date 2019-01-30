<?php

declare(strict_types = 1);

namespace App\Controller\Contact;

use App\Controller\BaseController;
use App\ExternalApi\Isite\Service\ContactPageService;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;

class ContactController extends BaseController
{
    public function __invoke(
        CoreEntity $coreEntity,
        ContactPageService $contactPageService
    ) {
        $this->setContextAndPreloadBranding($coreEntity);

        $isiteResult = $contactPageService->getContactPageByCoreEntity($coreEntity, false)->wait(true);
        if ($isiteResult->getTotal() === 0) {
            throw $this->createNotFoundException('No contact page found for pid');
        }
        $contactPages = $isiteResult->getDomainModels();
        $contactPage = $contactPages[0];

        return $this->renderWithChrome('contact/contact.html.twig', [
            'details' => $coreEntity->getOption('contact_details'),
            'ugcCampaignId' => $contactPage->getUgcCampaignId(),
        ]);
    }
}
