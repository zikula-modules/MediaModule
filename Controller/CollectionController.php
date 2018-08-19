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

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Form\Collection\CollectionType;
use Cmfcmf\Module\MediaModule\MediaType\UploadableMediaTypeInterface;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\RouteUrl;

class CollectionController extends AbstractController
{
    /**
     * @Route("/new/{slug}", requirements={"slug" = ".+"})
     * @Template(template="CmfcmfMediaModule:Collection:edit.html.twig")
     * @ParamConverter("parent", class="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", options={"slug" = "slug"})
     *
     * @param Request          $request
     * @param CollectionEntity $parent
     *
     * @return array|RedirectResponse
     */
    public function newAction(Request $request, CollectionEntity $parent)
    {
        if ($parent == null) {
            throw new NotFoundHttpException();
        }
        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        if (!$securityManager->hasPermission($parent, CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS)) {
            throw new AccessDeniedException();
        }

        $templateCollection = $this->get('cmfcmf_media_module.collection_template_collection');
        $entity = new CollectionEntity();
        $form = new CollectionType($templateCollection, $parent, $securityManager);
        $form->setTranslator($this->get('translator'));
        $form = $this->createForm($form, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->hookValidates('collection', 'validate_edit')) {
                if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity->getParent(), CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS)) {
                    throw new AccessDeniedException($this->__('You don\'t have permission to add a sub-collection to the selected parent collection.'));
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $this->applyProcessHook('collection', 'process_edit', $entity->getId(), new RouteUrl(
                    'cmfcmfmediamodule_collection_display',
                    ['slug' => $entity->getSlug()]
                ));

                return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);
            }
            $this->hookValidationError($form);
        }

        return [
            'form' => $form->createView(),
            'hook' => $this->getDisplayHookContent(
                'collection',
                'form_edit'
            ),
        ];
    }

    /**
     * @Route("/edit/{slug}", requirements={"slug" = ".+"})
     * @ParamConverter("entity", class="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", options={"slug" = "slug"})
     * @Template()
     *
     * @param Request          $request
     * @param CollectionEntity $entity
     *
     * @return array
     */
    public function editAction(Request $request, CollectionEntity $entity)
    {
        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        if (!$securityManager->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_COLLECTION)) {
            throw new AccessDeniedException();
        }

        $templateCollection = $this->get('cmfcmf_media_module.collection_template_collection');
        $form = new CollectionType($templateCollection, $entity->getParent(), $securityManager);
        $form->setTranslator($this->get('translator'));
        $form = $this->createForm($form, $entity);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            goto edit_error;
        }

        if (!$this->hookValidates('collection', 'validate_edit')) {
            $this->hookValidationError($form);
            goto edit_error;
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->merge($entity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->__('Someone else edited the collection. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        $this->applyProcessHook('collection', 'process_edit', $entity->getId(), new RouteUrl(
            'cmfcmfmediamodule_collection_display',
            ['slug' => $entity->getSlug()]
        ));

        return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);

        edit_error:

        return [
            'form' => $form->createView(),
            'hook' => $this->getDisplayHookContent(
                'collection',
                'form_edit',
                $entity->getId(),
                new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()])
            ),
        ];
    }

    /**
     * @Route("/download/{slug}.zip", requirements={"slug"=".+"})
     * @ParamConverter("entity", class="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", options={"slug" = "slug"})
     *
     * @param CollectionEntity $entity
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(CollectionEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_DOWNLOAD_COLLECTION)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();

        \CacheUtil::createLocalDir('CmfcmfMediaModule');
        $dir = \CacheUtil::getLocalDir('CmfcmfMediaModule');
        $path = $dir . '/' . uniqid(time(), true) . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE) !== true) {
            throw new ServiceUnavailableHttpException('Could not create zip archive!');
        }
        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        $hasContent = false;
        $usedFileNames = [];
        foreach ($entity->getMedia() as $media) {
            if ($media instanceof AbstractFileEntity && $media->isDownloadAllowed()) {
                /** @var UploadableMediaTypeInterface $mediaType */
                $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($media);

                $filename = $media->getBeautifiedFileName();
                $originalFileExtension = pathinfo($filename, PATHINFO_EXTENSION);
                $originalFilename = pathinfo($filename, PATHINFO_BASENAME);

                for ($i = 1; in_array($filename, $usedFileNames, true); ++$i) {
                    $filename = "$originalFilename ($i)" . (empty($originalFileExtension) ?: ".$originalFileExtension");
                }
                $zip->addFile($mediaType->getOriginalWithWatermark($media, 'path', false), $filename);
                $hasContent = true;

                $media->setDownloads($media->getDownloads() + 1);
                $em->merge($media);
            }
        }
        if (!$hasContent) {
            $zip->addFromString('Empty Collection.txt', $this->__('Sorry, the collection appears to be empty or does not have any downloadable files.'));
        }
        $zip->close();

        $entity->setDownloads($entity->getDownloads() + 1);
        $em->merge($entity);
        $em->flush();

        $response = new BinaryFileResponse($path);
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * @Route("/ajax/reorder", options={"expose" = true})
     *
     * @param Request $request
     *
     * @return PlainResponse
     */
    public function reorderAction(Request $request)
    {
        $id = $request->query->get('id');
        $oldPosition = $request->query->get('old-position');
        $newPosition = $request->query->get('new-position');
        $diff = $newPosition - $oldPosition;

        $repository = $this->getDoctrine()->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');
        $entity = $repository->find($id);

        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_COLLECTION)) {
            throw new AccessDeniedException();
        }

        if ($diff > 0) {
            $result = $repository->moveDown($entity, $diff);
        } else {
            $result = $repository->moveUp($entity, $diff * -1);
        }
        if (!$result) {
            throw new \RuntimeException();
        }

        return new PlainResponse();
    }

    /**
     * @Route("")
     * @Method("GET")
     *
     * @return RedirectResponse
     */
    public function displayRootAction()
    {
        $em = $this->getDoctrine()->getManager();
        $rootCollection = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')->getRootNode();

        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($rootCollection, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $rootCollection->getSlug()]);
    }

    /**
     * @Route("/{slug}", requirements={"slug"=".*[^/]"}, options={"expose" = true})
     * @ParamConverter("entity", class="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", options={"slug" = "slug"})
     * @Method("GET")
     * @Template()
     *
     * @param Request          $request
     * @param CollectionEntity $entity
     *
     * @return array
     */
    public function displayAction(Request $request, CollectionEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        if ($entity->getDefaultTemplate() != null) {
            $defaultTemplate = $entity->getDefaultTemplate();
        } else {
            $defaultTemplate = $this->getVar('defaultCollectionTemplate');
        }
        $template = $request->query->get('template', $defaultTemplate);

        $selectedTemplateFactory = $this->get('cmfcmf_media_module.collection_template.selected_factory');
        try {
            $selectedTemplate = $selectedTemplateFactory->fromDB($template);
        } catch (\DomainException $e) {
            throw new NotFoundHttpException();
        }

        if ($this->getVar('enableCollectionViewCounter', false)) {
            // Use query builder to update view count and thus avoid locking problems and race conditions.
            /** @var EntityManagerInterface $em */
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb->update('CmfcmfMediaModule:Collection\CollectionEntity', 'c')
                ->where($qb->expr()->eq('c.id', ':id'))
                ->set('c.views', 'c.views + 1')
                ->setParameter('id', $entity->getId())
                ->getQuery()->execute();
        }

        $templateVars = [
            'collection' => $entity,
            'breadcrumbs' => $entity->getBreadcrumbs($this->get('router'))
        ];

        $hookUrl = new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);
        $templateVars['hook'] = $this->getDisplayHookContent(
            'collection',
            'display_view',
            $entity->getId(),
            $hookUrl
        );
        $templateVars['renderRaw'] = $isHook = $request->query->get('isHook', false);

        $templateVars['content'] = $selectedTemplate->getTemplate()->render(
            $entity,
            $this->get('cmfcmf_media_module.media_type_collection'),
            !$isHook,
            $selectedTemplate->getOptions()
        );

        return $this->render('CmfcmfMediaModule:Collection:display.html.twig', $templateVars);
    }

    /**
     * @Route("/show-by-id/{id}", options={"expose" = true})
     *
     * @param CollectionEntity $entity
     *
     * @return RedirectResponse
     */
    public function displayByIdAction(CollectionEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        return $this->redirectToRoute(
            'cmfcmfmediamodule_collection_display',
            ['slug' => $entity->getSlug()]
        );
    }
}
