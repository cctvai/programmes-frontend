<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Controller\BaseController;
use App\ExternalApi\Uas\Service\UasService;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use Symfony\Component\HttpFoundation\Request;

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
            // Unauthorised
        }

        if ($programme->isRadio()) {
            // Sounds calls follows to containers subscriptions and follows to items bookmarks
            if ($programme instanceof ProgrammeContainer && !$programme->isTleo()) {
                // Sounds discourages subscribing to non-TLEOs
            }

            $resourceDomain = 'radio';
        } else if ($programme->isTv()) {
            if ($programme instanceof Clip) {
                // iPlayer doesn't have clips, so adding them is pointless
            }

            if (!$programme->isTleo()) {
                // iPlayer doesn't support non-TLEOs being added
            }

            $resourceDomain = 'tv';
        } else {
            // Think what to do when not TV or radio
        }

        $pid = (string) $programme->getPid();

        switch ($action) {
            case 'follow':
                if ($uasService->createActivity(
                    $idv5AccessToken,
                    'follows',
                    $resourceDomain,
                    $programme->getType(),
                    $pid
                )->wait()) {
                    return $this->redirect($this->generateUrl('find_by_pid', [
                        'pid' => $pid,
                    ]), 302);
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
                    return $this->redirect($this->generateUrl('find_by_pid', [
                        'pid' => $pid,
                    ]), 302);
                };
                break;
            default:
                // not possible lol
        }
    }
}
