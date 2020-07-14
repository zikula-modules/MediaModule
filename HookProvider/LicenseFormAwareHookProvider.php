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
use Cmfcmf\Module\MediaModule\Form\Type\EditLicenseType;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareHook;
use Zikula\Bundle\HookBundle\FormAwareHook\FormAwareResponse;

/**
 * License hook provider.
 */
class LicenseFormAwareHookProvider extends AbstractFormAwareHookProvider
{
    public function getTitle(): string
    {
        return $this->translator->trans('License FormAware hooks provider');
    }

    public function getProviderTypes(): array
    {
        return [
            FormAwareCategory::TYPE_EDIT => 'edit',
            FormAwareCategory::TYPE_PROCESS_EDIT => 'processEdit'
        ];
    }

    /**
     * Provide the inner editing form.
     */
    public function edit(FormAwareHook $hook): void
    {
        $repository = $this->entityManager->getRepository(LicenseEntity::class);
        $preferredLicenses = $repository->findBy(['outdated' => false]);
        $outdatedLicenses = $repository->findBy(['outdated' => true]);

        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);
        $selectedIds = array_map(function (LicenseEntity $licenseEntity) {
            return $licenseEntity->getId();
        }, $hookedObject->getLicenses()->getValues());

        $hook
            ->formAdd('cmfcmfmediamodule_hook_editlicense', EditLicenseType::class, [
                'auto_initialize' => false,
                'mapped' => false,
                'selectedLicenses' => $selectedIds,
                'preferredLicenses' => $preferredLicenses,
                'outdatedLicenses' => $outdatedLicenses
            ])
            ->addTemplate('@CmfcmfMediaModule/License/hookEdit.html.twig')
        ;
    }

    /**
     * Process the inner editing form.
     */
    public function processEdit(FormAwareResponse $hook): void
    {
        $innerForm = $hook->getFormData('cmfcmfmediamodule_hook_editlicense');
        $licenseIds = $innerForm['license'] ?? [];

        /** @var LicenseEntity[] $entities */
        $entities = [];
        foreach ($licenseIds as $licenseId) {
            if (empty($licenseId)) {
                continue;
            }
            $licenseEntity = $this->entityManager->find(LicenseEntity::class, $licenseId);
            if (!is_object($licenseEntity)) {
                //addError('license', $this->translator->trans('Unknown license', [], 'cmfcmfmediamodule'));
            } else {
                $entities[] = $licenseEntity;
            }
        }

        $repository = $this->entityManager->getRepository(HookedObjectEntity::class);
        $hookedObject = $repository->getByHookOrCreate($hook);

        $hookedObject->clearLicenses();
        foreach ($entities as $licenseEntity) {
            $hookedObject->addLicense($licenseEntity);
        }

        $repository->saveOrDelete($hookedObject);
    }

    public function getAreaName(): string
    {
        return 'provider.cmfcmfmediamodule.form_aware_hooks.licenses';
    }
}
