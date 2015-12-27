<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeInterface;
use Cmfcmf\Module\MediaModule\MediaType\PasteMediaTypeInterface;
use Cmfcmf\Module\MediaModule\MediaType\UploadableMediaTypeInterface;
use Cmfcmf\Module\MediaModule\MediaType\WebMediaTypeInterface;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\RouteUrl;
use Zikula\Core\Theme\Annotation\Theme;

class MediaController extends AbstractController
{
    /**
     * @Route("/admin/media-list/{page}", requirements={"page" = "\d+"})
     * @Method("GET")
     * @Template()
     * @Theme("admin")
     *
     * @param int $page
     *
     * @return array
     *
     * @todo Rename this + template to admin*L*istAction once the Routing PR is in the Core.
     */
    public function adminlistAction($page = 1)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('media', 'moderate')) {
            throw new AccessDeniedException();
        }
        if ($page < 1) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $perPage = 30;

        /** @var Paginator|AbstractMediaEntity[] $entities */
        $paginator = $em->getRepository('CmfcmfMediaModule:Media\AbstractMediaEntity')->getPaginated($page - 1, $perPage);
        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        return [
            'paginator' => $paginator,
            'mediaTypeCollection' => $mediaTypeCollection,
            'page' => $page,
            'maxPage' => ceil($paginator->count() / $perPage)
        ];
    }

    /**
     * @Route("/edit/{collectionSlug}/f/{slug}", requirements={"collectionSlug" = ".+?"})
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractMediaEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     * @Template()
     *
     * @param Request             $request
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function editAction(Request $request, AbstractMediaEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
                $entity,
                CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_MEDIA)
        ) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $parent = $request->query->get('parent', null);
        if ($parent != null) {
            $parent = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')->findOneBy(['slug' => $parent]);
        }

        $mediaType = $this->get('cmfcmf_media_module.media_type_collection')->getMediaTypeFromEntity($entity);
        $form = $mediaType->getFormTypeClass();
        $form = $this->createForm(new $form(false, $parent), $entity);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            goto edit_error;
        }

        if (!$this->hookValidates('media', 'validate_edit')) {
            $this->hookValidationError($form);
            goto edit_error;
        }

        $uploadManager = $this->get('stof_doctrine_extensions.uploadable.manager');
        $file = $form->has('file') ? $form->get('file')->getData() : null;
        if ($file !== null) {
            if (!($mediaType instanceof UploadableMediaTypeInterface)) {
                // Attempt to upload a file for a non-upload media type.
                throw new NotFoundHttpException();
            }
            if (!$mediaType->canUpload($file)) {
                $form->addError(new FormError($this->__('You must upload a file of the same type.')));
                goto edit_error;
            }

            $uploadManager->markEntityToUpload($entity, $file);

            // Cleanup thumbnails
            /** @var \SystemPlugin_Imagine_Manager $imagineManager */
            $imagineManager = $this->get('systemplugin.imagine.manager');
            $imagineManager->setModule('CmfcmfMediaModule');
            $imagineManager->removeObjectThumbs($entity->getImagineId());
        }

        try {
            $em->merge($entity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->__('Someone else edited the collection. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        $this->applyProcessHook('media', 'process_edit', $entity->getId(), new RouteUrl('cmfcmfmediamodule_media_display', ['slug' => $entity->getSlug()]));

        $isPopup = $request->query->get('popup', false);
        if ($isPopup) {
            return $this->redirectToRoute('cmfcmfmediamodule_media_popupembed', ['id' => $entity->getId()]);
        }

        return $this->redirectToRoute('cmfcmfmediamodule_media_display', ['slug' => $entity->getSlug(), 'collectionSlug' => $entity->getCollection()->getSlug()]);

        edit_error:

        return [
            'form' => $form->createView(),
            'breadcrumbs' => $entity->getCollection()->getBreadcrumbs($this->get('router')),
            'hook' => $this->getDisplayHookContent(
                'media',
                'form_edit',
                $entity->getId(),
                new RouteUrl('cmfcmfmediamodule_media_display', ['slug' => $entity->getSlug()])
            ),
            'entity' => $entity
        ];
    }

    /**
     * @Route("/delete/{collectionSlug}/f/{slug}", requirements={"collectionSlug" = ".+?"})
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractMediaEntity", options={"repository_method"="findBySlugs", "map_method_signature"=true})
     * @Template()
     *
     * @param Request             $request
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function deleteAction(Request $request, AbstractMediaEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_DELETE_MEDIA)
        ) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            if ($this->hookValidates('media', 'validate_delete')) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($entity);
                $em->flush();

                // @todo Delete file if appropriate.

                $this->applyProcessHook(
                    'media',
                    'process_delete',
                    $entity->getId(),
                    new RouteUrl('cmfcmfmediamodule_media_display', ['slug' => $entity->getSlug()])
                );

                return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $entity->getCollection()->getSlug()]);
            } else {
                /** @var \Zikula_Session $session */
                $session = $request->getSession();
                $session->getFlashbag()->add('error', $this->__('Hook validation failed!'));
            }
        }
        $breadcrumbs = $entity->getCollection()->getBreadcrumbs($this->get('router'));

        return [
            'breadcrumbs' => $breadcrumbs,
            'entity' => $entity,
            'hook' => $this->getDisplayHookContent(
                'media',
                'form_delete',
                $entity->getId(),
                new RouteUrl('cmfcmfmediamodule_media_edit', ['slug' => $entity->getSlug(), 'collectionSlug' => $entity->getCollection()->getSlug()])
            )
        ];
    }

    /**
     * @Route("/media/new")
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function newAction(Request $request)
    {
        $this->checkMediaCreationAllowed();

        $isPopup = $request->query->filter('popup', false, false, FILTER_VALIDATE_BOOLEAN);
        $parentCollectionSlug = $request->query->get('parent', null);

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        $collections = $this->get('cmfcmf_media_module.security_manager')
            ->getCollectionsWithAccessQueryBuilder(
                CollectionPermissionSecurityTree::PERM_LEVEL_ADD_MEDIA
            )
            ->getQuery()
            ->execute();

        return [
            'webMediaTypes' => $mediaTypeCollection->getWebMediaTypes(true),
            'collections' => $collections,
            'parentCollectionSlug' => $parentCollectionSlug,
            'isPopup' => $isPopup
        ];
    }

    /**
     * @Route("/media/create/{type}/{mediaType}/{collection}", options={"expose"=true})
     * @Template()
     *
     * @param Request $request
     * @param $type
     * @param $mediaType
     * @param null $collection
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request, $type, $mediaType, $collection = null)
    {
        if (!in_array($type, ['paste', 'web', 'upload'])) {
            throw new NotFoundHttpException();
        }
        $this->checkMediaCreationAllowed();

        $init = $request->request->get('init', false);
        $mediaType = $this->get('cmfcmf_media_module.media_type_collection')->getMediaType($mediaType);
        $entity = $this->getDefaultEntity($request, $type, $mediaType, $init, $collection);

        $form = $mediaType->getFormTypeClass();
        $form = $this->createForm(new $form(true), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->hookValidates('media', 'validate_edit')) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $this->applyProcessHook(
                    'media',
                    'process_edit',
                    $entity->getId(),
                    new RouteUrl('cmfcmfmediamodule_media_display', [
                        'slug' => $entity->getSlug(),
                        'collectionSlug' => $entity->getCollection()->getSlug()
                    ])
                );

                if ($request->query->get('popup', false)) {
                    return $this->redirectToRoute(
                        'cmfcmfmediamodule_media_popupembed',
                        ['id' => $entity->getId()]
                    );
                }

                return $this->redirectToRoute('cmfcmfmediamodule_media_display', [
                    'collectionSlug' => $entity->getCollection()->getSlug(),
                    'slug' => $entity->getSlug()
                ]);
            }
            $this->hookValidationError($form);
        }

        return [
            'form' => $form->createView(),
            'hook' => $this->getDisplayHookContent(
                'media',
                'form_edit'
            )
        ];
    }

    /**
     * @Route("/media/ajax/matches-paste", options={"expose" = true})
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function matchesPasteAction(Request $request)
    {
        $this->checkMediaCreationAllowed();

        $pastedText = $request->request->get('pastedText', false);
        if ($pastedText === false) {
            throw new NotFoundHttpException();
        }

        $pasteMediaTypes = $this->get('cmfcmf_media_module.media_type_collection')->getPasteMediaTypes();
        $matches = [];
        foreach ($pasteMediaTypes as $pasteMediaType) {
            $score = $pasteMediaType->matchesPaste($pastedText);
            if ($score > 0) {
                $arr = $pasteMediaType->toArray();
                $arr['score'] = $score;
                $matches[] = $arr;
            }
        }
        usort($matches, function ($a, $b) {
            return $a['score'] - $b['score'];
        });

        return new JsonResponse($matches);
    }

    /**
     * @Route("/media/ajax/reorder", options={"expose" = true})
     *
     * @param Request $request
     *
     * @return PlainResponse
     */
    public function reorderAction(Request $request)
    {
        $id = $request->query->get('id');
        $position = $request->query->get('position');

        $entity = $this
            ->getDoctrine()
            ->getRepository('CmfcmfMediaModule:Media\AbstractMediaEntity')
            ->find($id);

        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_MEDIA)
        ) {
            throw new AccessDeniedException();
        }

        $entity->setPosition($position);

        $em = $this->getDoctrine()->getManager();
        $em->merge($entity);
        $em->flush();

        return new PlainResponse();
    }

    /**
     * @Route("/media/ajax/creation-results/web/{mediaType}", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function webCreationAjaxResultsAction(Request $request, $mediaType)
    {
        $this->checkMediaCreationAllowed();

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        try {
            $mediaType = $mediaTypeCollection->getMediaType($mediaType);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException();
        }
        if (!($mediaType instanceof WebMediaTypeInterface)) {
            throw new NotFoundHttpException();
        }
        $q = $request->request->get('q', false);
        if ($q === false) {
            throw new NotFoundHttpException();
        }
        $dropdownValue = $request->request->get('dropdownValue', null);
        if ($dropdownValue == "") {
            $dropdownValue = null;
        }

        $results = $mediaType->getSearchResults($request, $q, $dropdownValue);

        return new JsonResponse($results);
    }

    /**
     * @Route("/media/ajax/get-media-type", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getMediaTypeFromFileAction(Request $request)
    {
        $this->checkMediaCreationAllowed();

        $mediaTypes = $this->get('cmfcmf_media_module.media_type_collection')->getUploadableMediaTypes();
        $files = $request->request->get('files', false);
        if ($files === false) {
            throw new NotFoundHttpException();
        }
        $result = [];
        $notFound = 0;
        $multiple = false;
        $lastResult = -1;
        foreach ($files as $c => $file) {
            $max = 0;
            $selectedMediaType = null;
            foreach ($mediaTypes as $mediaType) {
                $n = $mediaType->canUploadArr($file);
                if ($n > $max) {
                    $max = $n;
                    $selectedMediaType = $mediaType;
                }
            }
            if ($selectedMediaType === null) {
                $result[$c] = null;
                $notFound++;
            } else {
                $result[$c] = $selectedMediaType->getAlias();

                if ($lastResult != -1 && $lastResult != $result[$c]) {
                    $multiple = true;
                }
                $lastResult = $result[$c];
            }
        }

        return new JsonResponse([
            'result' => $result,
            'multiple' => $multiple,
            'notFound' => $notFound
        ]);
    }

    /**
     * Endpoint for file uploads.
     *
     * @Route("/media/upload", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function uploadAction(Request $request)
    {
        $this->checkMediaCreationAllowed();

        try {
            $mediaTypes = $this->get('cmfcmf_media_module.media_type_collection')->getUploadableMediaTypes();
            $uploadManager = $this->get('stof_doctrine_extensions.uploadable.manager');
            $em = $this->getDoctrine()->getManager();

            $collection = $request->request->get('collection', null);
            if ($collection == null) {
                $collection = CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID;
            }

            if ($request->files->count() != 1) {
                return new Response(null, Response::HTTP_BAD_REQUEST);
            }

            /** @var UploadedFile $file */
            $file = current($request->files->all());
            if (!$file->isValid()) {
                return new Response($this->__('The upload was corrupted. Please try again!'), Response::HTTP_BAD_REQUEST);
            }
            $max = 0;
            $selectedMediaType = null;
            foreach ($mediaTypes as $mediaType) {
                $n = $mediaType->canUpload($file);
                if ($n > $max) {
                    $max = $n;
                    $selectedMediaType = $mediaType;
                }
            }
            if ($selectedMediaType === null) {
                return new Response($this->__('File type not supported!'), Response::HTTP_FORBIDDEN);
            }

            /** @var AbstractFileEntity $entity */
            $entity = $selectedMediaType->getEntityClass();
            $entity = new $entity();

            $form = $selectedMediaType->getFormTypeClass();
            $form = $this->createForm(new $form(true, null, true), $entity, ['csrf_protection' => false]);
            $form->remove('file');

            $form->submit([
                'title' => str_replace('_', ' ', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'collection' => $collection
            ], false);

            if (!$form->isValid()) {
                return new Response($this->__('Invalid data, errors: ') . $form->getErrors(true)->__toString(), Response::HTTP_BAD_REQUEST);
            }

            $uploadManager->markEntityToUpload($entity, $file);
            $em->persist($entity);
            $em->flush();

            return new JsonResponse([
                'msg' => $this->__('File uploaded!'),
                'editUrl' => $this->generateUrl('cmfcmfmediamodule_media_edit', ['slug' => $entity->getSlug(), 'collectionSlug' => $entity->getCollection()->getSlug()]),
                'openNewTabAndEdit' => $collection == CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/media/popup-embed/{id}")
     * @Template()
     * @Method("GET")
     *
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function popupEmbedAction(AbstractMediaEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS)
        ) {
            throw new AccessDeniedException();
        }

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($entity);

        return [
            'embedCodes' => [
                'full' => $mediaType->getEmbedCode($entity, 'full'),
                'medium' => $mediaType->getEmbedCode($entity, 'medium'),
                'small' => $mediaType->getEmbedCode($entity, 'small')
            ]
        ];
    }

    /**
     * @Route("/download/{collectionSlug}/f/{slug}", requirements={"collectionSlug" = ".+?"})
     * @Method("GET")
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractFileEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     *
     * @param AbstractFileEntity $entity
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(AbstractFileEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM)
        ) {
            throw new AccessDeniedException();
        }

        if (!$entity->isDownloadAllowed()) {
            throw new NotFoundHttpException();
        }

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        /** @var UploadableMediaTypeInterface $mediaType */
        $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($entity);

        $response = new BinaryFileResponse($mediaType->getOriginalWithWatermark($entity, 'path', false));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $entity->getBeautifiedFileName());

        return $response;
    }

    /**
     * @Route("/show/{collectionSlug}/f/{slug}", requirements={"collectionSlug" = ".+?"}, options={"expose" = true})
     * @Method("GET")
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractMediaEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     * @Template()
     *
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function displayAction(AbstractMediaEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS)
        ) {
            throw new AccessDeniedException();
        }

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        return [
            'mediaType' => $mediaTypeCollection->getMediaTypeFromEntity($entity),
            'entity' => $entity,
            'breadcrumbs' =>  $entity->getCollection()->getBreadcrumbs($this->get('router'), true),
            'hook' => $this->getDisplayHookContent(
                'media',
                'display_view',
                $entity->getId(),
                new RouteUrl('cmfcmfmediamodule_media_display', ['slug' => $entity->getSlug()])
            )
        ];
    }

    private function checkMediaCreationAllowed()
    {
        $securityManager = $this->get('cmfcmf_media_module.security_manager');

        $qb = $securityManager->getCollectionsWithAccessQueryBuilder(
            CollectionPermissionSecurityTree::PERM_LEVEL_ADD_MEDIA
        );
        $qb->setMaxResults(1);

        try {
            $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new AccessDeniedException();
        }
    }

    private function getDefaultEntity(Request $request, $type, MediaTypeInterface $mediaType, $init, $collection)
    {
        if (!$init) {
            $entity = $mediaType->getEntityClass();
            $entity = new $entity();

            return $entity;
        }
        switch ($type) {
            case 'web':
                try {
                    /** @var MediaTypeInterface|WebMediaTypeInterface $mediaType */
                    $entity = $mediaType->getEntityFromWeb($request);
                } catch (\Exception $e) {
                    throw new NotFoundHttpException();
                }
                break;
            case 'paste':
                $pastedText = $request->request->get('pastedText', false);
                if (empty($pastedText)) {
                    throw new NotFoundHttpException();
                }
                /** @var MediaTypeInterface|PasteMediaTypeInterface $mediaType */
                $entity = $mediaType->getEntityFromPaste($pastedText);
                break;
            default:
                throw new \LogicException();
        }
        if ($collection !== null) {
            $entity->setCollection($this->getDoctrine()->getManager()->find('CmfcmfMediaModule:Collection\CollectionEntity', $collection));
        }

        return $entity;
    }
}
