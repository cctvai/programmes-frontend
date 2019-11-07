<?php

namespace App\Controller\JSON;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\ApsMapper\MapperInterface;

abstract class BaseApsController extends AbstractController
{

    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $response = parent::json($data, $status, $headers, $context);
        $response->setPublic()->setMaxAge(600);
        return $response;
    }

    protected function mapSingleApsObject(MapperInterface $apsMapper, $domainEntity, ...$additionalArgs)
    {
        if (is_null($domainEntity)) {
            return null;
        }

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $apsMapper->getApsObject($domainEntity, ...$additionalArgs);
    }

    protected function mapManyApsObjects(MapperInterface $apsMapper, $domainEntities)
    {
        $apsObjects = [];
        foreach ($domainEntities as $domainEntity) {
            $mappedObject = $this->mapSingleApsObject($apsMapper, $domainEntity);

            if (is_array($mappedObject)) {
                foreach ($mappedObject as $item) {
                    $apsObjects[] = $item;
                }
            } else {
                $apsObjects[] = $mappedObject;
            }
        }
        return $apsObjects;
    }
}
