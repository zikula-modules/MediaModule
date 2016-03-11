<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Exception\UpgradeFailedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Theme\Annotation\Theme;

class UpgradeController extends AbstractController
{
    /**
     * @Route("/settings/upgrade")
     * @Template()
     * @Theme("admin")
     *
     * @return array|RedirectResponse
     */
    public function doUpgradeAction()
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('settings', 'admin')) {
            throw new AccessDeniedException();
        }

        $hasPermission = \SecurityUtil::checkPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN);
        $upgrader = $this->get('cmfcmf_media_module.upgrade.module_upgrader');

        return [
            'steps' => $upgrader->getUpgradeSteps(),
            'hasPermission' => $hasPermission
        ];
    }

    /**
     * @Route("/settings/upgrade/ajax/{step}", options={"expose" = true})
     *
     * @param $step
     *
     * @return JsonResponse
     */
    public function ajaxAction($step)
    {
        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        if (!$securityManager->hasPermission('settings', 'admin') || !$securityManager->hasPermissionRaw('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        set_time_limit(60);

        $upgrader = $this->get('cmfcmf_media_module.upgrade.module_upgrader');
        $versionChecker = $this->get('cmfcmf_media_module.upgrade.version_checker');

        try {
            $upgradeDone = $upgrader->upgrade($step, $versionChecker);
            if ($upgradeDone) {
                \ModUtil::setVar('CmfcmfMediaModule', 'newVersionAvailable', false);
                \ModUtil::setVar('CmfcmfMediaModule', 'lastNewVersionCheck', 0);
            }

            return new JsonResponse([
                'proceed' => true,
                'message' => null,
                'done' => $upgradeDone
            ]);
        } catch (UpgradeFailedException $e) {
            return new JsonResponse([
                'proceed' => false,
                'message' => $e->getMessage(),
                'done' => false
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'proceed' => false,
                'message' => $this->get('translator')->trans(
                    'Something unexpected happened. Please report this problem and give the following information: %s',
                    ['%s' => (string)$e],
                    'cmfcmfmediamodule'),
                'done' => false
            ]);
        }
    }
}
