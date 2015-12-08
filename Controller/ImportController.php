<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Form\ImportType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Theme\Annotation\Theme;

class ImportController extends AbstractController
{
    /**
     * @Route("/import")
     * @Template()
     * @Theme("admin")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function selectAction(Request $request)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('import', 'admin')) {
            throw new AccessDeniedException();
        }

        $importerCollection = $this->get('cmfcmf_media_module.importer_collection');

        return [
            'importers' => $importerCollection->getImporters()
        ];
    }

    /**
     * @Route("/import/{importer}")
     * @Template()
     *
     * @param Request $request
     * @param $importer
     *
     * @return array
     */
    public function executeAction(Request $request, $importer)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('import', 'admin')) {
            throw new AccessDeniedException();
        }

        $importerCollection = $this->get('cmfcmf_media_module.importer_collection');
        if (!$importerCollection->hasImporter($importer)) {
            throw new NotFoundHttpException();
        }
        $importer = $importerCollection->getImporter($importer);

        $form = $this->createForm(new ImportType($importer->getSettingsForm(), $this->get('translator'), $this->get('kernel')->getModule('CmfcmfMediaModule')->getTranslationDomain()));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $success = $importer->import($form->getData());

            if ($success === true) {
                $this->addFlash('status', $this->__('Media imported successfully.'));

                return $this->redirectToRoute('cmfcmfmediamodule_collection_display', $form->getData()['collection']->getSlug());
            } else if (is_string($success)) {
                $this->addFlash('error', $success);
            } else {
                $this->addFlash('error', $this->__('An unexpected error occurred while importing.'));
            }
        }

        return [
            'form' => $form->createView(),
            'importer' => $importer
        ];
    }
}

