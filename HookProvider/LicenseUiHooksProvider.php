<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\HookProvider;

use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationResponse;

/**
 * License ui hooks provider.
 */
class LicenseUiHooksProvider extends AbstractUiHooksProvider
{
    /**
     * @var LicenseEntity[]
     */
    private $entities;

    public function getTitle()
    {
        return $this->translator->__('License ui hooks provider');
    }

    public function getProviderTypes()
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'uiView',
            UiHooksCategory::TYPE_FORM_EDIT => 'uiEdit',
            UiHooksCategory::TYPE_VALIDATE_EDIT => 'validateEdit',
            UiHooksCategory::TYPE_PROCESS_EDIT => 'processEdit',
            UiHooksCategory::TYPE_PROCESS_DELETE => 'processDelete'
        ];
    }

    /**
     * @param DisplayHook $hook
     */
    public function uiView(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $content = $this->renderEngine->render('@CmfcmfMediaModule/License/hookView.html.twig', [
            'licenses' => $hookedObject->getLicenses()
        ]);

        $hook->setResponse(new DisplayHookResponse('provider.cmfcmfmediamodule.ui_hooks.licenses', $content));
    }

    /**
     * @param DisplayHook $hook
     */
    public function uiEdit(DisplayHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:License\LicenseEntity');
        $preferredLicenses = $repository->findBy(['outdated' => false]);
        $outdatedLicenses = $repository->findBy(['outdated' => true]);

        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);
        $selectedIds = array_map(function (LicenseEntity $licenseEntity) {
            return $licenseEntity->getId();
        }, $hookedObject->getLicenses()->getValues());

        $content = $this->renderEngine->render('@CmfcmfMediaModule/License/hookEdit.html.twig', [
            'selectedLicenses' => $selectedIds,
            'preferredLicenses' => $preferredLicenses,
            'outdatedLicenses' => $outdatedLicenses
        ]);

        $hook->setResponse(new DisplayHookResponse('provider.cmfcmfmediamodule.ui_hooks.licenses', $content));
    }

    /**
     * @param ValidationHook $hook
     */
    public function validateEdit(ValidationHook $hook)
    {
        $licenseIds = $this->requestStack->getCurrentRequest()
            ->request->get('cmfcmfmediamodule[license]', [], true);

        $this->entities = [];
        $validationResponse = new ValidationResponse('license', $licenseIds);
        foreach ($licenseIds as $licenseId) {
            if (!empty($licenseId)) {
                $licenseEntity = $this->entityManager->find('CmfcmfMediaModule:License\LicenseEntity', $licenseId);
                if (!is_object($licenseEntity)) {
                    $validationResponse->addError('license', $this->translator->trans('Unknown license', [], 'cmfcmfmediamodule'));
                } else {
                    $this->entities[] = $licenseEntity;
                }
            }
        }

        $hook->setValidator($this->getProvider(), $validationResponse);
    }

    /**
     * @param ProcessHook $hook
     */
    public function processEdit(ProcessHook $hook)
    {
        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
        $hookedObject = $repository->getByHookOrCreate($hook);

        $hookedObject->clearLicenses();
        foreach ($this->entities as $licenseEntity) {
            $hookedObject->addLicense($licenseEntity);
        }

        $repository->saveOrDelete($hookedObject);
    }
}
