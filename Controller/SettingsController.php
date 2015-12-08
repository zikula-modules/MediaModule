<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Form\SettingsType;
use Cmfcmf\Module\MediaModule\MediaModuleInstaller;
use Github\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Theme\Annotation\Theme;

class SettingsController extends AbstractController
{
    /**
     * @Route("/settings", options={"expose" = true})
     * @Template()
     * @Theme("admin")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function settingsAction(Request $request)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('settings', 'admin')) {
            throw new AccessDeniedException();
        }

        if ($request->query->get('update', false)) {
            $this->get('zikula.doctrine.schema_tool')->update(
                MediaModuleInstaller::getEntities()
            );
        }

        $collectionTemplateCollection = $this->get('cmfcmf_media_module.collection_template_collection');

        $form = $this->createForm(new SettingsType($collectionTemplateCollection->getCollectionTemplateTitles()));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            foreach ($data as $name => $value) {
                \ModUtil::setVar('CmfcmfMediaModule', $name, $value);
            }
            $this->addFlash('status', $this->__('Settings saved!'));
        }

        $scribiteInstalled = \ModUtil::available('Scribite');
        $descriptionEscapingStrategyForCollectionOk = true;
        $descriptionEscapingStrategyForMediaOk = true;

        if ($scribiteInstalled) {
            $mediaBinding = $this->get('hook_dispatcher')->getBindingBetweenAreas(
                "subscriber.cmfcmfmediamodule.ui_hooks.media", "provider.scribite.ui_hooks.editor");
            $collectionBinding = $this->get('hook_dispatcher')->getBindingBetweenAreas(
                "subscriber.cmfcmfmediamodule.ui_hooks.collection", "provider.scribite.ui_hooks.editor");

            $descriptionEscapingStrategyForCollectionOk =  !is_object($collectionBinding)
                || \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection') == 'raw';
            $descriptionEscapingStrategyForMediaOk = !is_object($mediaBinding)
                || \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForMedia') == 'raw';
        }

        return [
            'form' => $form->createView(),
            'scribiteInstalled' => $scribiteInstalled,
            'descriptionEscapingStrategyForCollectionOk' => $descriptionEscapingStrategyForCollectionOk,
            'descriptionEscapingStrategyForMediaOk' => $descriptionEscapingStrategyForMediaOk
        ];
    }

    /**
     * @Route("/settings/upgrade")
     * @Template()
     * @Theme("admin")
     *
     * @return array|RedirectResponse
     */
    public function upgradeAction()
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
     * @return JsonResponse
     */
    public function ajaxUpgradeAction($step)
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
