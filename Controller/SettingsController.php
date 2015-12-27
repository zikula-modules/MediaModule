<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Form\SettingsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
}
