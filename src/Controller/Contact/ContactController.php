<?php
declare(strict_types = 1);

namespace App\Controller\Contact;

use App\Controller\BaseController;
use App\Controller\Helpers\Breadcrumbs;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;
use BBC\ProgrammesPagesService\Domain\ValueObject\UGCContactDetails;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContactController extends BaseController
{
    public function __invoke(
        CoreEntity $coreEntity,
        Breadcrumbs $breadcrumbs
    ) {
        $pid = $coreEntity->getPid();
        $this->setContextAndPreloadBranding($coreEntity);
        $this->setAtiContentLabels('admin-contact', 'list-contact');
        $this->setAtiContentId((string) $pid, 'pips');

        $contactDetails = $coreEntity->getOption('contact_details');
        if (!$contactDetails) {
            throw new NotFoundHttpException('No contact details found');
        }

        $contactDetails = [] + $contactDetails; // avoid usort() mutating the source option

        usort($contactDetails, function (ContactDetails $entry1, ContactDetails $entry2) {
            switch (true) {
                case $entry1 instanceof UGCContactDetails:
                    return -1;
                case $entry2 instanceof UGCContactDetails:
                    return 1;
                default:
                    return 0;
            }
        });

        $parameters = [
            'contactDetails' => $contactDetails,
            'currentUrl' => $this->request()->getUri(),
        ];

        $this->breadcrumbs = $breadcrumbs
            ->forNetwork($coreEntity->getNetwork())
            ->forEntityAncestry($coreEntity)
            ->forRoute('Contact', 'programme_contact', ['pid' => $pid])
            ->toArray();

        return $this->renderWithChrome('contact/index.html.twig', $parameters);
    }
}
