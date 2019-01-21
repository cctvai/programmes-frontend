<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\ExternalApi\Isite\Domain\ContactPage;
use App\ExternalApi\Isite\WrongEntityTypeException;
use SimpleXMLElement;

class ContactPageMapper extends Mapper
{
    public function getDomainModel(SimpleXMLElement $isiteObject): ContactPage
    {
        $form = $this->getForm($isiteObject);
        $formMetaData = $this->getFormMetaData($isiteObject);
        $projectSpace = $this->getProjectSpace($formMetaData);
        $resultMetaData = $this->getMetaData($isiteObject);
        $guid = $this->getString($resultMetaData->guid);
        $ugcCampaignId = null;
        if (isset($form->content->{'ugc_campaign_id'}) && (string) $form->content->{'ugc_campaign_id'}) {
            $ugcCampaignId = (string) $form->content->{'ugc_campaign_id'};
        }
        if (!$this->isContact($resultMetaData)) {
            throw new WrongEntityTypeException(
                sprintf(
                    "iSite form with guid %s attempted to be mapped as contact page, but is not a contact page, is a %s",
                    $guid,
                    (string) $resultMetaData->type
                )
            );
        }
        return new ContactPage($ugcCampaignId);
    }

    private function isContact(SimpleXMLElement $resultMetaData)
    {
        return (isset($resultMetaData->type) && (string) $resultMetaData->type === 'programmes-contact');
    }
}
