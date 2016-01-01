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

use Github\Exception\RuntimeException;
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
        $steps = [
            'php-check' => [
                'text' => $this->__('Checking server requirements'),
                'icon' => 'fa-server'
            ],
            'version-check' => [
                'text' => $this->__('Checking installed and available versions'),
                'icon' => 'fa-github'
            ],
            'permission-check' => [
                'text' => $this->__('Checking file system permissions'),
                'icon' => 'fa-files-o'
            ],
            'download' => [
                'text' => $this->__('Downloading new version'),
                'icon' => 'fa-download'
            ],
            'extracting' => [
                'text' => $this->__('Extracting new version'),
                'icon' => 'fa-file-archive-o'
            ],
            'upgrading' => [
                'text' => $this->__('Running upgrade'),
                'icon' => 'fa-code'
            ],
        ];

        return [
            'steps' => $steps,
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

        switch ($step) {
            case 'php-check':
                $proceed = $upgrader->checkRequirements();
                break;
            case 'version-check':
                try {
                    if (!$versionChecker->checkRateLimit()) {
                        $proceed = $this->__('Your GitHub API Rate limit is exceeded. Please try again later.');
                        break;
                    }
                    $info = \ModUtil::getInfoFromName('CmfcmfMediaModule');
                    $release = $versionChecker->getReleaseToUpgradeTo($info['version']);
                    if ($release === false) {
                        $proceed = $this->__('No release to upgrade to available!');
                    } else {
                        $proceed = true;
                    }
                } catch (RuntimeException $e) {
                    // Something went wrong with the GitHub API.
                    $proceed = $this->__('Could not connect to GitHub.');
                }
                break;
            case 'permission-check':
                $proceed = $upgrader->checkPermissions();
                break;
            case 'download':
                if (!$versionChecker->checkRateLimit()) {
                    $proceed = $this->__('Your GitHub API Rate limit is exceeded. Please try again later.');
                    break;
                }
                $info = \ModUtil::getInfoFromName('CmfcmfMediaModule');
                $release = $versionChecker->getReleaseToUpgradeTo($info['version']);
                if ($release === false) {
                    $proceed = $this->__('No release to upgrade to available!');
                } else {
                    foreach ($release['assets'] as $asset) {
                        if (in_array($asset['content_type'], ['application/x-zip', 'application/zip'])) {
                            break;
                        }
                    }
                    if (!isset($asset)) {
                        $proceed = $this->__('Something went wrong. The release doesn\'t contain a ZIP asset.');
                    } else {
                        $proceed = $upgrader->downloadNewVersion($asset['browser_download_url']);
                    }
                }
                break;
            case 'extracting':
                $proceed = $upgrader->extractNewVersion();
                break;
            case 'upgrading':
                $proceed = $upgrader->upgrade();

                \ModUtil::setVar('CmfcmfMediaModule', 'newVersionAvailable', false);
                \ModUtil::setVar('CmfcmfMediaModule', 'lastNewVersionCheck', 0);
                break;
            default:
                $proceed = $this->__('Invalid step received');
                break;
        }

        return new JsonResponse([
            'proceed' => $proceed === true,
            'message' => is_string($proceed) ? $proceed : null
        ]);
    }
}
