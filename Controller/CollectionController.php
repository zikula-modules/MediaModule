<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Form\Collection\CollectionType;
use Cmfcmf\Module\MediaModule\MediaType\UploadableMediaTypeInterface;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
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
     * @Route("/new/{slug}", requirements={"slug" = ".+"}, defaults={"slug" = null})
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
        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        if (!$securityManager->hasPermission($parent, CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS)) {
            throw new AccessDeniedException();
        }

        $templateCollection = $this->get('cmfcmf_media_module.collection_template_collection');
        $entity = new CollectionEntity();
        $form = $this->createForm(new CollectionType($templateCollection, $parent, $securityManager), $entity);
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
        $form = $this->createForm(new CollectionType($templateCollection, $entity->getParent(), $securityManager), $entity);
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
     * @return array
     */
    public function downloadAction(CollectionEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_DOWNLOAD_COLLECTION)) {
            throw new AccessDeniedException();
        }

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
            }
        }
        if (!$hasContent) {
            $zip->addFromString('Empty Collection.txt', $this->__('Sorry, the collection appears to be empty or does not have any downloadable files.'));
        }
        $zip->close();

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
     * @Template()
     *
     * @return array
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
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     * @param         $slug
     *
     * @return array
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     */
    public function displayAction(Request $request, $slug)
    {
        $qb = $this
            ->getDoctrine()
            ->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')
            ->createQueryBuilder('c');

        // Fetching all the media here is necessary because it otherwise does a query per
        // thumbnail check to see if a thumbnail exists.
        // @todo Make the thumbnail selectable using an association and fetch it eagerly.
        $qb->select(['c', 'cc', 'm'])
            ->where($qb->expr()->eq('c.slug', ':slug'))
            ->setParameter('slug', $slug)
            ->leftJoin('c.children', 'cc')
            ->leftJoin('cc.media', 'm')
        ;
        $entity = $qb->getQuery()->getSingleResult();

        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        if ($entity->getDefaultTemplate() != null) {
            $defaultTemplate = $entity->getDefaultTemplate();
        } else {
            $defaultTemplate = \ModUtil::getVar('CmfcmfMediaModule', 'defaultCollectionTemplate');
        }

        $template = $request->query->get('template', $defaultTemplate);
        $collectionTemplateCollection = $this->get('cmfcmf_media_module.collection_template_collection');
        if (!$collectionTemplateCollection->hasTemplate($template)) {
            throw new NotFoundHttpException();
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

        $templateVars['content'] = $collectionTemplateCollection->getCollectionTemplate($template)->render(
            $entity,
            $this->get('cmfcmf_media_module.media_type_collection'),
            !$isHook
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
