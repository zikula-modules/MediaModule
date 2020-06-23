<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\HookProvider;

use Cmfcmf\Module\MediaModule\Entity\HookedObject\HookedObjectEntity;
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

    public function getTitle(): string
    {
        return $this->translator->trans('License ui hooks provider');
    }

    public function getProviderTypes(): array
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

        $content = $this->twig->render('@CmfcmfMediaModule/License/hookView.html.twig', [
            'licenses' => $hookedObject->getLicenses()
        ]);

        $hook->setResponse(new DisplayHookResponse($this->getAreaName(), $content));
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

        $content = $this->twig->render('@CmfcmfMediaModule/License/hookEdit.html.twig', [
            'selectedLicenses' => $selectedIds,
            'preferredLicenses' => $preferredLicenses,
            'outdatedLicenses' => $outdatedLicenses
        ]);

        $hook->setResponse(new DisplayHookResponse($this->getAreaName(), $content));
    }

    /**
     * @param ValidationHook $hook
     */
    public function validateEdit(ValidationHook $hook)
    {
        include_once __DIR__ . '/../bootstrap.php';

        // TODO migrate this to a FormAware hook provider
        $licenseIds = $this->requestStack->getCurrentRequest()
            ->request->get('cmfcmfmediamodule[license]', [], true);
        $licenseIds = $_POST['cmfcmfmediamodule']['license'] ?? [];

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

        $hook->setValidator($this->getAreaName(), $validationResponse);
    }

    /**
     * @param ProcessHook $hook
     */
    public function processEdit(ProcessHook $hook)
    {
        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $hookedObject->clearLicenses();
        foreach ($this->entities as $licenseEntity) {
            $hookedObject->addLicense($licenseEntity);
        }

        $repository->saveOrDelete($hookedObject);
    }

    public function getAreaName(): string
    {
        return 'provider.cmfcmfmediamodule.ui_hooks.licenses';
    }
}
