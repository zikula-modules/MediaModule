<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Form\Collection\CollectionType;
use Cmfcmf\Module\MediaModule\MediaType\UploadableMediaTypeInterface;
use Doctrine\ORM\OptimisticLockException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\RouteUrl;

class CollectionController extends AbstractController
{
    /**
     * @Route("/new/{slug}", requirements={"slug" = ".+"}, defaults={"slug" = null})
     * @Template(template="CmfcmfMediaModule:Collection:Edit.html.twig")
     * @ParamConverter("parent", class="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", options={"slug" = "slug"})
     *
     * @param Request          $request
     * @param CollectionEntity $parent
     *
     * @return array|RedirectResponse
     */
    public function newAction(Request $request, CollectionEntity $parent = null)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('collection', 'new')) {
            throw new AccessDeniedException();
        }

        $templateCollection = $this->get('cmfcmf_media_module.collection_template_collection');
        $entity = new CollectionEntity();
        $form = $this->createForm(new CollectionType($templateCollection, $parent), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->hookValidates('collection', 'validate_edit')) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $this->applyProcessHook('collection', 'process_edit', $entity->getId(), new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]));

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
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, 'edit')) {
            throw new AccessDeniedException();
        }

        $templateCollection = $this->get('cmfcmf_media_module.collection_template_collection');
        $form = $this->createForm(new CollectionType($templateCollection, $entity->getParent()), $entity);
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

        $this->applyProcessHook('collection', 'process_edit', $entity->getId(), new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]));

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
     * @param Request          $request
     * @param CollectionEntity $entity
     *
     * @return array
     */
    public function downloadAction(CollectionEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, 'download')) {
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
     * @Route("")
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function displayRootAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $rootCollections = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')->getRootNodes();
        $rootCollections = array_filter($rootCollections, function (CollectionEntity $entity) {
            return $entity->getId() != CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID;
        });

        // Build fake root entity.
        $entity = new CollectionEntity();
        $entity
            ->setTitle($this->__('Root collections'))
            ->setChildren($rootCollections)
            ->setVirtualRoot(true)
        ;

        return $this->displayAction($request, $entity);
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
        return $this->redirectToRoute(
            'cmfcmfmediamodule_collection_display',
            ['slug' => $entity->getSlug()]
        );
    }

    /**
     * @Route("/show/{slug}", requirements={"slug"=".+"}, options={"expose" = true})
     * @Method("GET")
     * @ParamConverter("entity", class="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", options={"slug" = "slug"})
     * @Template()
     *
     * @param Request          $request
     * @param CollectionEntity $entity
     *
     * @return array
     */
    public function displayAction(Request $request, CollectionEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity !== null ? $entity : 'collection', 'display')) {
            throw new AccessDeniedException();
        }
        if ($entity->getId() == CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID) {
            throw new NotFoundHttpException();
        }

        $template = $request->query->get('template', $entity->getDefaultTemplate() != null ? $entity->getDefaultTemplate() : \ModUtil::getVar('CmfcmfMediaModule', 'defaultCollectionTemplate'));
        $collectionTemplateCollection = $this->get('cmfcmf_media_module.collection_template_collection');
        if (!$collectionTemplateCollection->hasTemplate($template)) {
            throw new NotFoundHttpException();
        }

        $templateVars = [
            'collection' => $entity,
            'breadcrumbs' => $entity->getBreadcrumbs($this->get('router'))
        ];

        if ($entity->isVirtualRoot()) {
            $hookUrl = new RouteUrl('cmfcmfmediamodule_collection_displayroot');
        } else {
            $hookUrl = new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);
        }
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

        return $this->render('CmfcmfMediaModule:Collection:Display.html.twig', $templateVars);
    }
}
