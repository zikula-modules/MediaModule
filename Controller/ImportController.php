<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Form\ImportType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class ImportController extends AbstractController
{
    /**
     * @Route("/import")
     * @Template()
     * @Theme("admin")
     *
     * @return array
     */
    public function selectAction()
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
     * @Theme("admin")
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

        $importType = new ImportType($importer->getSettingsForm(), $this->get('translator'), $this->get('cmfcmf_media_module.security_manager'));
        $form = $this->createForm($importType);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $success = $importer->import($form->getData(), $this->get('session')->getFlashBag());

            if ($success === true) {
                $this->addFlash('status', $this->__('Media imported successfully.'));

                return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $form->getData()['collection']->getSlug()]);
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

