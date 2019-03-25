<?php
declare (strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use App\Controller\Helpers\ProducerVariableHelper;
use App\Controller\Helpers\DestinationVariableHelper;

class AtiAnalyticsLabels
{
    /** @var mixed */
    private $context;

    /** @var string */
    private $appEnvironment;

    /** @var array */
    private $extraLabels;

    /** @var string */
    private $contentType;

    /** @var string */
    private $chapterOne;
  
    /** @var ProducerVariableHelper */
    private $producerVariableHelper;

    /** @var DestinationVariableHelper */
    private $destinationVariableHelper;

    /** @var string|null */
    private $contentId;
  
    /** @var string|null */
    private $overriddenEntityTitle;

    public function __construct(ProducerVariableHelper $producerVariableHelper, DestinationVariableHelper $destinationVariableHelper, $context, CosmosInfo $cosmosInfo, array $extraLabels, string $contentType, string $chapterOne, string $contentId = null, string $overriddenEntityTitle = null)
    {
        $this->context = $context;
        $this->appEnvironment = $cosmosInfo->getAppEnvironment();
        $this->extraLabels = $extraLabels;
        $this->contentType = $contentType;
        $this->chapterOne = $chapterOne;
        $this->contentId = $contentId;
        $this->producerVariableHelper = $producerVariableHelper;
        $this->destinationVariableHelper = $destinationVariableHelper;
        $this->overriddenEntityTitle = $overriddenEntityTitle;
    }

    public function orbLabels()
    {
        $brandCustomVar = $this->calculateCustomVarBrand();
        $masterbrandCustomVar = $this->calculateCustomVarMid();
        $entityTitle = $this->calculateEntityTitle();

        if ($brandCustomVar) {
            $brandCustomVar = urlencode($brandCustomVar);
        }

        if ($masterbrandCustomVar) {
            $masterbrandCustomVar = urlencode($masterbrandCustomVar);
        }
      
        if ($entityTitle) {
            $entityTitle = urlencode($entityTitle);
        }

        $producer = 'BBC';
        if ($this->context) {
            $producer = $this->producerVariableHelper->calculateProducerVariable($this->context);
        }

        $destination = 'programmes_ps';
        if ($this->context) {
            $destination = $this->destinationVariableHelper->getDestinationFromContext($this->context, $this->appEnvironment);
        }

        $labels = [
            'destination' => $destination,
            'producer' => $producer,
            'contentType' => $this->contentType,
            'section' => $this->chapterOne,
            'contentId' => $this->contentId,
            'additionalProperties' => [
                ['name' => 'app_name', 'value' => 'programmes'],
                ['name' => 'custom_var_1', 'value' => $entityTitle],
                ['name' => 'custom_var_2', 'value' => $brandCustomVar],
                ['name' => 'custom_var_4', 'value' => $masterbrandCustomVar],
            ],
        ];

        $labels = array_merge($labels, $this->extraLabels);

        return $labels;
    }

    private function calculateCustomVarBrand(): ?string
    {
        if ($this->context instanceof CoreEntity && $this->context->getTleo() && $this->context->getTleo()->isTlec()) {
            return $this->context->getTleo()->getTitle();
        }
        return null;
    }

    private function calculateCustomVarMid(): ?string
    {
        if ($this->context instanceof CoreEntity && !empty($this->context->getMasterBrand())) {
            return (string) $this->context->getMasterBrand()->getMid();
        }
        if ($this->context instanceof Service && !empty($this->context->getNetwork())) {
            return (string) $this->context->getNetwork()->getNid();
        }

        return null;
    }

    private function calculateEntityTitle(): ?string
    {
        if ($this->overriddenEntityTitle) {
            return $this->overriddenEntityTitle;
        }

        if (!$this->context) {
            return null;
        }

        if ($this->context instanceof Service) {
            return $this->context->getName();
        }

        return $this->context->getTitle();
    }
}
