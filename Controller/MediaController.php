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
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeInterface;
use Cmfcmf\Module\MediaModule\MediaType\PasteMediaTypeInterface;
use Cmfcmf\Module\MediaModule\MediaType\UploadableMediaTypeInterface;
use Cmfcmf\Module\MediaModule\MediaType\WebMediaTypeInterface;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\RouteUrl;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class MediaController extends AbstractController
{
    /**
     * @Route("/admin/media-list/{page}", methods={"GET"}, requirements={"page" = "\d+"})
     * @Template("CmfcmfMediaModule:Media:adminlist.html.twig")
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
     * @Template("CmfcmfMediaModule:Media:edit.html.twig")
     *
     * @param Request             $request
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function editAction(Request $request, AbstractMediaEntity $entity)
    {
        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        $editPermission = $securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_MEDIA
        );
        $isTemporaryUploadCollection = $entity->getCollection()->getId() == CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID;
        $justUploadedIds = $request->getSession()->get('cmfcmfmediamodule_just_uploaded', []);

        if (!$editPermission && !($isTemporaryUploadCollection && in_array($entity->getId(), $justUploadedIds))) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $parent = $request->query->get('parent', null);
        if ($parent != null) {
            $parent = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')->findOneBy(['slug' => $parent]);
        }

        $mediaType = $this->get('cmfcmf_media_module.media_type_collection')->getMediaTypeFromEntity($entity);
        $form = $mediaType->getFormTypeClass();
        $formOptions = $mediaType->getFormOptions($entity);
        $formOptions['parent'] = $parent;
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm($form, $entity, $formOptions);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            goto edit_error;
        }

        if (!$this->hookValidates('media', UiHooksCategory::TYPE_VALIDATE_EDIT)) {
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

            /** TODO migrate
            // Cleanup existing thumbnails
            /** @var Liip\ImagineBundle\Imagine\Cache\CacheManager $imagineCacheManager * /
            $imagineCacheManager = $this->get('liip_imagine.cache.manager');

            $imagineManager->removeObjectThumbs($entity->getImagineId());
            */
        }

        try {
            $em->merge($entity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->__('Someone else edited the collection. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        $hookUrl = new RouteUrl('cmfcmfmediamodule_media_display', [
            'slug' => $entity->getSlug(),
            'collectionSlug' => $entity->getCollection()->getSlug()
        ]);
        $this->applyFormAwareProcessHook($form, 'media', FormAwareCategory::TYPE_PROCESS_EDIT, $entity, $hookUrl);
        $this->applyProcessHook('media', UiHooksCategory::TYPE_PROCESS_EDIT, $entity->getId(), $hookUrl);

        $isPopup = $request->query->get('popup', false);
        if ($isPopup) {
            return $this->redirectToRoute('cmfcmfmediamodule_media_popupembed', ['id' => $entity->getId()]);
        }

        return $this->redirectToRoute('cmfcmfmediamodule_media_display', ['slug' => $entity->getSlug(), 'collectionSlug' => $entity->getCollection()->getSlug()]);

        edit_error:

        $hookUrl = new RouteUrl('cmfcmfmediamodule_media_display', [
            'slug' => $entity->getSlug(),
            'collectionSlug' => $entity->getCollection()->getSlug()
        ]);
        $formHook = $this->applyFormAwareDisplayHook($form, 'media', FormAwareCategory::TYPE_EDIT, $entity->getId(), $hookUrl);

        return [
            'form' => $form->createView(),
            'entity' => $entity,
            'breadcrumbs' => $entity->getCollection()->getBreadcrumbs($this->get('router')),
            'hook' => $this->getDisplayHookContent('media', UiHooksCategory::TYPE_FORM_EDIT, $entity->getId(), $hookUrl),
            'formHookTemplates' => $formHook->getTemplates()
        ];
    }

    /**
     * @Route("/delete/{collectionSlug}/f/{slug}", requirements={"collectionSlug" = ".+?"})
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractMediaEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     * @Template("CmfcmfMediaModule:Media:delete.html.twig")
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
            if ($this->hookValidates('media', UiHooksCategory::TYPE_VALIDATE_DELETE)) {
                // Save entity id for use in hook event. It is set to null during the entitymanager flush.
                $id = $entity->getId();

                $em = $this->getDoctrine()->getManager();
                $em->remove($entity);
                $em->flush();

                // @todo Delete file if appropriate.

                $hookUrl = new RouteUrl('cmfcmfmediamodule_media_display', [
                    'slug' => $entity->getSlug(),
                    'collectionSlug' => $entity->getCollection()->getSlug()
                ]);
                $this->applyProcessHook('media', UiHooksCategory::TYPE_PROCESS_DELETE, $id, $hookUrl);

                return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $entity->getCollection()->getSlug()]);
            } else {
                $request->getSession()->getFlashbag()->add('error', $this->__('Hook validation failed!'));
            }
        }

        $hookUrl = new RouteUrl('cmfcmfmediamodule_media_edit', [
            'slug' => $entity->getSlug(),
            'collectionSlug' => $entity->getCollection()->getSlug()
        ]);

        return [
            'entity' => $entity,
            'breadcrumbs' => $entity->getCollection()->getBreadcrumbs($this->get('router')),
            'hook' => $this->getDisplayHookContent('media', UiHooksCategory::TYPE_FORM_DELETE, $entity->getId(), $hookUrl)
        ];
    }

    /**
     * @Route("/media/new", methods={"GET"})
     * @Template("CmfcmfMediaModule:Media:new.html.twig")
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
            ->execute()
        ;

        return [
            'webMediaTypes' => $mediaTypeCollection->getWebMediaTypes(true),
            'collections' => $collections,
            'parentCollectionSlug' => $parentCollectionSlug,
            'isPopup' => $isPopup
        ];
    }

    /**
     * @Route("/media/create/{type}/{mediaType}/{collection}", options={"expose"=true})
     * @Template("CmfcmfMediaModule:Media:create.html.twig")
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

        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $em = $this->getDoctrine()->getManager();

        $init = $request->request->get('init', false);
        $mediaType = $this->get('cmfcmf_media_module.media_type_collection')->getMediaType($mediaType);
        $entity = $this->getDefaultEntity($request, $type, $mediaType, $init, $collection);

        $form = $mediaType->getFormTypeClass();
        $formOptions = $mediaType->getFormOptions($entity);
        $formOptions['isCreation'] = true;
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm($form, $entity, $mediaType->getFormOptions($entity));
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->hookValidates('media', UiHooksCategory::TYPE_VALIDATE_EDIT)) {
                $em->persist($entity);
                $em->flush();

                $hookUrl = new RouteUrl('cmfcmfmediamodule_media_display', [
                    'slug' => $entity->getSlug(),
                    'collectionSlug' => $entity->getCollection()->getSlug()
                ]);
                $this->applyFormAwareProcessHook($form, 'media', FormAwareCategory::TYPE_PROCESS_EDIT, $entity, $hookUrl);
                $this->applyProcessHook('media', UiHooksCategory::TYPE_PROCESS_EDIT, $entity->getId(), $hookUrl);

                if ($request->query->get('popup', false)) {
                    return $this->redirectToRoute('cmfcmfmediamodule_media_popupembed', ['id' => $entity->getId()]);
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
            'hook' => $this->getDisplayHookContent('media', UiHooksCategory::TYPE_FORM_EDIT)
        ];
    }

    /**
     * @Route("/media/ajax/matches-paste", methods={"POST"}, options={"expose" = true})
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
            return $b['score'] - $a['score'];
        });

        return $this->json($matches);
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
     * @Route("/media/ajax/creation-results/web/{mediaType}", methods={"POST"}, options={"expose"=true})
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
        if ('' == $dropdownValue) {
            $dropdownValue = null;
        }

        $results = $mediaType->getSearchResults($q, $dropdownValue);

        return $this->json($results);
    }

    /**
     * @Route("/media/ajax/get-media-type", methods={"POST"}, options={"expose"=true})
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
                $n = $mediaType->mightUpload($file['mimeType'], $file['size'], $file['name']);
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

        return $this->json([
            'result' => $result,
            'multiple' => $multiple,
            'notFound' => $notFound
        ]);
    }

    /**
     * Endpoint for file uploads.
     *
     * @Route("/media/upload", methods={"POST"}, options={"expose"=true})
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function uploadAction(Request $request)
    {
        $this->checkMediaCreationAllowed();

        try {
            $securityManager = $this->get('cmfcmf_media_module.security_manager');
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

            $dataDirectory = $this->get('service_container')->getParameter('datadir');

            /** @var AbstractFileEntity $entity */
            $entity = $selectedMediaType->getEntityClass();
            $entity = new $entity($this->get('request_stack'), $dataDirectory);

            $form = $selectedMediaType->getFormTypeClass();
            $formOptions = $mediaType->getFormOptions($entity);
            $formOptions['isCreation'] = true;
            $formOptions['allowTemporaryUploadCollection'] = true;
            $formOptions['csrf_protection'] = false;
            /** @var \Symfony\Component\Form\Form $form */
            $form = $this->createForm($form, $entity, $formOptions);
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

            $justUploadedIds = $request->getSession()->get('cmfcmfmediamodule_just_uploaded', []);
            $justUploadedIds[] = $entity->getId();
            $request->getSession()->set('cmfcmfmediamodule_just_uploaded', $justUploadedIds);

            return $this->json([
                'msg' => $this->__('File uploaded!'),
                'editUrl' => $this->generateUrl('cmfcmfmediamodule_media_edit', ['slug' => $entity->getSlug(), 'collectionSlug' => $entity->getCollection()->getSlug()]),
                'openNewTabAndEdit' => $collection == CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/media/popup-embed/{id}", methods={"GET"})
     * @Template("CmfcmfMediaModule:Media:popupEmbed.html.twig")
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
     * @Route("/download/{collectionSlug}/f/{slug}", methods={"GET"}, requirements={"collectionSlug" = ".+?"})
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

        $em = $this->getDoctrine()->getManager();
        $entity->setDownloads($entity->getDownloads() + 1);
        $em->merge($entity);
        $em->flush();

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        /** @var UploadableMediaTypeInterface $mediaType */
        $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($entity);

        $response = new BinaryFileResponse($mediaType->getOriginalWithWatermark($entity, 'path', false));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $entity->getBeautifiedFileName());

        return $response;
    }

    /**
     * @Route("/{collectionSlug}/f/{slug}", methods={"GET"}, requirements={"collectionSlug" = ".+?"}, options={"expose" = true})
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractMediaEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     * @Template("CmfcmfMediaModule:Media:display.html.twig")
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

        if ($this->getVar('enableMediaViewCounter', false)) {
            // Use query builder to update view count and thus avoid locking problems and race conditions.
            /** @var EntityManagerInterface $em */
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb->update('CmfcmfMediaModule:Media\AbstractMediaEntity', 'm')
                ->where($qb->expr()->eq('m.id', ':id'))
                ->set('m.views', 'm.views + 1')
                ->setParameter('id', $entity->getId())
                ->getQuery()->execute();
        }

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');
        $hookUrl = new RouteUrl('cmfcmfmediamodule_media_display', [
            'slug' => $entity->getSlug(),
            'collectionSlug' => $entity->getCollection()->getSlug()
        ]);

        return [
            'mediaType' => $mediaTypeCollection->getMediaTypeFromEntity($entity),
            'entity' => $entity,
            'breadcrumbs' =>  $entity->getCollection()->getBreadcrumbs($this->get('router'), true),
            'views' => $this->getVar('enableMediaViewCounter', false) ? $entity->getViews() : '-1',
            'hook' => $this->getDisplayHookContent('media', UiHooksCategory::TYPE_DISPLAY_VIEW, $entity->getId(), $hookUrl)
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
            $dataDirectory = $this->get('service_container')->getParameter('datadir');
            $entity = $mediaType->getEntityClass();
            $entity = new $entity($this->get('request_stack'), $dataDirectory);

            return $entity;
        }
        switch ($type) {
            case 'web':
                try {
                    /** @var MediaTypeInterface|WebMediaTypeInterface $mediaType */
                    $entity = $mediaType->getEntityFromWeb();
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
        if (null !== $collection) {
            $entity->setCollection($this->getDoctrine()->getManager()->find('CmfcmfMediaModule:Collection\CollectionEntity', $collection));
        }

        return $entity;
    }
}
