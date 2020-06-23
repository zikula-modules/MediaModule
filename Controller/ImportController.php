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

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Form\ImportType;
use Cmfcmf\Module\MediaModule\Importer\ImporterCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class ImportController extends AbstractController
{
    /**
     * @Route("/import")
     * @Template("@CmfcmfMediaModule/Import/select.html.twig")
     * @Theme("admin")
     *
     * @return array
     */
    public function selectAction(ImporterCollection $importerCollection)
    {
        if (!$this->securityManager->hasPermission('import', 'admin')) {
            throw new AccessDeniedException();
        }

        return [
            'importers' => $importerCollection->getImporters()
        ];
    }

    /**
     * @Route("/import/{importer}")
     * @Template("@CmfcmfMediaModule/Import/execute.html.twig")
     * @Theme("admin")
     *
     * @param Request $request
     * @param $importer
     *
     * @return array
     */
    public function executeAction(Request $request, ImporterCollection $importerCollection, $importer)
    {
        if (!$this->securityManager->hasPermission('import', 'admin')) {
            throw new AccessDeniedException();
        }

        if (!$importerCollection->hasImporter($importer)) {
            throw new NotFoundHttpException();
        }
        $importer = $importerCollection->getImporter($importer);
        if (true !== $importer->checkRequirements()) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(ImportType::class, null, ['importerForm' => $importer->getSettingsForm()]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $success = $importer->import($form->getData(), $this->get('session')->getFlashBag());

            if (true === $success) {
                $this->addFlash('status', $this->trans('Media imported successfully.'));

                return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $form->getData()['collection']->getSlug()]);
            } elseif (is_string($success)) {
                $this->addFlash('error', $success);
            } else {
                $this->addFlash('error', $this->trans('An unexpected error occurred while importing.'));
            }
        }

        return [
            'form' => $form->createView(),
            'importer' => $importer
        ];
    }
}
