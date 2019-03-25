<?php
declare(strict_types = 1);

namespace App\Controller\Helpers;

class DestinationVariableHelper
{
    public function getDestinationFromContext($context, $appEnvironment): string
    {
        $destination =  'programmes_ps';

        if (method_exists($context, 'getNetwork') && $context->getNetwork()
            && (in_array((string) $context->getNetwork()->getNid(), ['bbc_world_service', 'bbc_world_service_tv', 'bbc_learning_english'])
                || $context->getNetwork()->isWorldServiceInternational())
        ) {
            $destination = 'ws_programmes';
        }

        if (in_array($appEnvironment, ['int', 'stage', 'sandbox', 'test'])) {
            $destination .= '_test';
        }

        return $destination;
    }
}
