<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Controller\BaseController;
use App\ExternalApi\Uas\Service\UasService;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ActionController extends BaseController
{
    public function __invoke(
        Programme $programme,
        string $action,
        Request $request,
        UasService $uasService
    ) {
        $this->setAtiContentLabels('very', 'cool');

        $idv5AccessToken = $request->cookies->get('ckns_atkn');
        if (!$idv5AccessToken) {
            throw new HttpException(401, 'IDv5 access token cookie missing.');
        }

        $pid = (string) $programme->getPid();

        $redirect = $request->request->get(
            'redirect',
            $this->generateUrl('find_by_pid', [
                'pid' => $pid,
            ])
        );

        if ($programme->isRadio()) {
            if ($programme instanceof ProgrammeContainer && !$programme->isTleo()) {
                throw $this->createNotFoundException('Sounds subscriptions must only be to TLECs.');
            }

            $resourceDomain = 'radio';
        } else if ($programme->isTv()) {
            if ($programme instanceof Clip) {
                throw $this->createNotFoundException('iPlayer doesn\'t support clips.');
            }

            if (!$programme->isTleo()) {
                throw $this->createNotFoundException('Only TLEOs can be added to iPlayer.');
            }

            $resourceDomain = 'tv';
        } else {
            throw $this->createNotFoundException('Only TV and Radio programmes can be followed.');
        }

        switch ($action) {
            case 'follow':
                if ($uasService->createActivity(
                    $idv5AccessToken,
                    'follows',
                    $resourceDomain,
                    $programme->getType(),
                    $pid
                )->wait()) {
                    return $this->redirect($redirect, 302);
                }
                break;
            case 'unfollow':
                if ($uasService->deleteActivity(
                    $idv5AccessToken,
                    'follows',
                    $resourceDomain,
                    $programme->getType(),
                    $pid
                )->wait()) {
                    return $this->redirect($redirect, 302);
                };
                break;
            default:
                throw $this->createNotFoundException('Action "' . $action . '" is not supported.');
        }
    }
}
