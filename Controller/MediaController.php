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

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
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
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
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
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\CoreBundle\RouteUrl;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class MediaController extends AbstractController
{
    /**
     * @Route("/admin/media-list/{page}", methods={"GET"}, requirements={"page" = "\d+"})
     * @Template("@CmfcmfMediaModule/Media/adminlist.html.twig")
     * @Theme("admin")
     *
     * @param int $page
     *
     * @return array
     *
     * @todo Rename this + template to admin*L*istAction once the Routing PR is in the Core.
     */
    public function adminlistAction(Request $request, $page = 1)
    {
        if (!$this->securityManager->hasPermission('media', 'moderate')) {
            throw new AccessDeniedException();
        }
        if ($page < 1) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $perPage = 30;
        $q = trim($request->query->get('q', ''));

        /** @var Paginator|AbstractMediaEntity[] $entities */
        $paginator = $em->getRepository('CmfcmfMediaModule:Media\AbstractMediaEntity')->getPaginated($page - 1, $perPage, $q);
        $mediaTypeCollection = $this->mediaTypeCollection;

        return [
            'paginator' => $paginator,
            'mediaTypeCollection' => $mediaTypeCollection,
            'page' => $page,
            'maxPage' => ceil($paginator->count() / $perPage),
            'currentSearchTerm' => $q
        ];
    }

    /**
     * @Route("/edit/{collectionSlug}/f/{slug}", requirements={"collectionSlug" = ".+?"})
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractMediaEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     * @Template("@CmfcmfMediaModule/Media/edit.html.twig")
     *
     * @param Request             $request
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function editAction(
        Request $request,
        CollectionRepository $collectionRepository,
        AbstractMediaEntity $entity
    ) {
        $editPermission = $this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_MEDIA
        );
        $isTemporaryUploadCollection = CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID === $entity->getCollection()->getId();
        $justUploadedIds = $request->getSession()->get('cmfcmfmediamodule_just_uploaded', []);

        if (!$editPermission && !($isTemporaryUploadCollection && in_array($entity->getId(), $justUploadedIds))) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $parent = $request->query->get('parent', null);
        if (null !== $parent) {
            $parent = $collectionRepository->findOneBy(['slug' => $parent]);
        }

        $mediaType = $this->mediaTypeCollection->getMediaTypeFromEntity($entity);
        $formClass = $mediaType->getFormTypeClass();
        $formOptions = $mediaType->getFormOptions($entity);
        $formOptions['parent'] = $parent;
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm($formClass, $entity, $formOptions);
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
        if (null !== $file) {
            if (!($mediaType instanceof UploadableMediaTypeInterface)) {
                // Attempt to upload a file for a non-upload media type.
                throw new NotFoundHttpException();
            }
            if (!$mediaType->canUpload($file)) {
                $form->addError(new FormError($this->trans('You must upload a file of the same type.')));
                goto edit_error;
            }

            $uploadManager->markEntityToUpload($entity, $file);

            // Cleanup existing thumbnails
            /** @var Liip\ImagineBundle\Imagine\Cache\CacheManager $imagineCacheManager */
            //$imagineCacheManager = $this->get('liip_imagine.cache.manager');
            //$imagineCacheManager->remove($entity->getPath(), ['thumbnail', 'cmfcmfmediamodule.custom_image_filter']);
        }

        try {
            $em->merge($entity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->trans('Someone else edited the collection. Please either cancel editing or force reload the page.')));
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
        $formHook = $this->applyFormAwareDisplayHook($form, 'media', FormAwareCategory::TYPE_EDIT, $hookUrl);

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
     * @Template("@CmfcmfMediaModule/Media/delete.html.twig")
     *
     * @param Request             $request
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function deleteAction(Request $request, AbstractMediaEntity $entity)
    {
        if (!$this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_DELETE_MEDIA
        )
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
            }
            $request->getSession()->getFlashbag()->add('error', $this->trans('Hook validation failed!'));
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
     * @Template("@CmfcmfMediaModule/Media/new.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function newAction(Request $request)
    {
        $this->checkMediaCreationAllowed();

        $isPopup = $request->query->filter('popup', false, FILTER_DEFAULT, FILTER_VALIDATE_BOOLEAN);
        $parentCollectionSlug = $request->query->get('parent', null);

        $mediaTypeCollection = $this->mediaTypeCollection;

        $collections = $this->securityManager
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
     * @Template("@CmfcmfMediaModule/Media/create.html.twig")
     *
     * @param Request $request
     * @param $type
     * @param $mediaType
     * @param null $collection
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(
        Request $request,
        string $projectDir,
        string $type,
        $mediaType,
        $collection = null
    ) {
        if (!in_array($type, ['paste', 'web', 'upload'])) {
            throw new NotFoundHttpException();
        }
        $this->checkMediaCreationAllowed();

        $em = $this->getDoctrine()->getManager();

        $init = $request->request->get('init', false);
        $mediaType = $this->mediaTypeCollection->getMediaType($mediaType);
        $entity = $this->getDefaultEntity($request, $type, $mediaType, $init, $collection, $projectDir . '/public/uploads');

        $formClass = $mediaType->getFormTypeClass();
        $formOptions = $mediaType->getFormOptions($entity);
        $formOptions['isCreation'] = true;
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm($formClass, $entity, $mediaType->getFormOptions($entity));
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
        if (false === $pastedText) {
            throw new NotFoundHttpException();
        }

        $pasteMediaTypes = $this->mediaTypeCollection->getPasteMediaTypes();
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

        if (!$this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_MEDIA
        )
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

        $mediaTypeCollection = $this->mediaTypeCollection;

        try {
            $mediaType = $mediaTypeCollection->getMediaType($mediaType);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException();
        }
        if (!($mediaType instanceof WebMediaTypeInterface)) {
            throw new NotFoundHttpException();
        }
        $q = $request->request->get('q', false);
        if (false === $q) {
            throw new NotFoundHttpException();
        }
        $dropdownValue = $request->request->get('dropdownValue', null);
        if ('' === $dropdownValue) {
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

        $mediaTypes = $this->mediaTypeCollection->getUploadableMediaTypes();
        $files = $request->request->get('files', false);
        if (false === $files) {
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
            if (null === $selectedMediaType) {
                $result[$c] = null;
                $notFound++;
            } else {
                $result[$c] = $selectedMediaType->getAlias();

                if (-1 !== $lastResult && $lastResult !== $result[$c]) {
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
    public function uploadAction(Request $request, UploadableManager $uploadableManager)
    {
        $this->checkMediaCreationAllowed();

        try {
            $mediaTypes = $this->mediaTypeCollection->getUploadableMediaTypes();
            $em = $this->getDoctrine()->getManager();

            $collection = $request->request->get('collection', null);
            if (null === $collection) {
                $collection = CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID;
            }

            if (1 !== $request->files->count()) {
                return new Response(null, Response::HTTP_BAD_REQUEST);
            }

            /** @var UploadedFile $file */
            $file = current($request->files->all());
            if (!$file->isValid()) {
                return new Response($this->trans('The upload was corrupted. Please try again!'), Response::HTTP_BAD_REQUEST);
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
            if (null === $selectedMediaType) {
                return new Response($this->trans('File type not supported!'), Response::HTTP_FORBIDDEN);
            }

            $dataDirectory = $this->get('service_container')->getParameter('datadir');

            /** @var AbstractFileEntity $entity */
            $entity = $selectedMediaType->getEntityClass();
            $entity = new $entity($this->get('request_stack'), $dataDirectory);

            $formClass = $selectedMediaType->getFormTypeClass();
            $formOptions = $mediaType->getFormOptions($entity);
            $formOptions['isCreation'] = true;
            $formOptions['allowTemporaryUploadCollection'] = true;
            $formOptions['csrf_protection'] = false;
            /** @var \Symfony\Component\Form\Form $form */
            $form = $this->createForm($formClass, $entity, $formOptions);
            $form->remove('file');

            $form->submit([
                'title' => str_replace('_', ' ', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'collection' => $collection
            ], false);

            if (!$form->isValid()) {
                return new Response($this->trans('Invalid data, errors: ') . $form->getErrors(true)->__toString(), Response::HTTP_BAD_REQUEST);
            }

            $uploadableManager->markEntityToUpload($entity, $file);
            $em->persist($entity);
            $em->flush();

            $justUploadedIds = $request->getSession()->get('cmfcmfmediamodule_just_uploaded', []);
            $justUploadedIds[] = $entity->getId();
            $request->getSession()->set('cmfcmfmediamodule_just_uploaded', $justUploadedIds);

            return $this->json([
                'msg' => $this->trans('File uploaded!'),
                'editUrl' => $this->generateUrl('cmfcmfmediamodule_media_edit', ['slug' => $entity->getSlug(), 'collectionSlug' => $entity->getCollection()->getSlug()]),
                'openNewTabAndEdit' => CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID === $collection
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/media/popup-embed/{id}", methods={"GET"})
     * @Template("@CmfcmfMediaModule/Media/popupEmbed.html.twig")
     *
     * @param Request $request
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function popupEmbedAction(Request $request, AbstractMediaEntity $entity)
    {
        if (!$this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS
        )
        ) {
            throw new AccessDeniedException();
        }

        return $this->getEmbedDataAction($request, $entity);
    }

    /**
     * @Route("/media/embed-data/{id}", methods={"GET"}, options={"expose"=true})
     *
     * @param Request $request
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function getEmbedDataAction(Request $request, AbstractMediaEntity $entity)
    {
        if (!$this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS
        )
        ) {
            throw new AccessDeniedException();
        }

        $mediaTypeCollection = $this->mediaTypeCollection;

        $class = get_class($entity);
        $type = mb_substr($class, mb_strrpos($class, '\\') + 1, -mb_strlen('Entity'));
        $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($entity);

        $preview = '';
        if ('Image' === $type) {
            $preview = $mediaType->renderFullpage($entity);
        }

        $result = [
            'title' => $entity->getTitle(),
            'preview' => $preview,
            'slug' => $entity->getSlug(),
            'embedCodes' => [
                'full' => $mediaType->getEmbedCode($entity, 'full'),
                'medium' => $mediaType->getEmbedCode($entity, 'medium'),
                'small' => $mediaType->getEmbedCode($entity, 'small')
            ],
            'collection' => $entity->getCollection()
        ];

        if (!$request->isXmlHttpRequest()) {
            return $result;
        }

        return $this->json($result);
    }

    /**
     * @Route("/download/{collectionSlug}/f/{slug}", methods={"GET"}, requirements={"collectionSlug" = ".+?"}, options={"expose"=true})
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractFileEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     *
     * @param AbstractFileEntity $entity
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(Request $request, AbstractFileEntity $entity)
    {
        if (!$this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_DOWNLOAD_SINGLE_MEDIUM
        )
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

        $mediaTypeCollection = $this->mediaTypeCollection;

        /** @var UploadableMediaTypeInterface $mediaType */
        $mediaType = $mediaTypeCollection->getMediaTypeFromEntity($entity);

        $response = new BinaryFileResponse($mediaType->getOriginalWithWatermark($entity, 'path', false));

        if ($request->query->has('inline') && '1' === $request->query->get('inline', '0')) {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $entity->getBeautifiedFileName());
        } else {
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $entity->getBeautifiedFileName());
        }

        return $response;
    }

    /**
     * @Route("/{collectionSlug}/f/{slug}", methods={"GET"}, requirements={"collectionSlug" = ".+?"}, options={"expose" = true})
     * @ParamConverter("entity", class="CmfcmfMediaModule:Media\AbstractMediaEntity", options={"repository_method" = "findBySlugs", "map_method_signature" = true})
     * @Template("@CmfcmfMediaModule/Media/display.html.twig")
     *
     * @param AbstractMediaEntity $entity
     *
     * @return array
     */
    public function displayAction(AbstractMediaEntity $entity)
    {
        if (!$this->securityManager->hasPermission(
            $entity,
            CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS
        )
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

        $mediaTypeCollection = $this->mediaTypeCollection;
        $hookUrl = new RouteUrl('cmfcmfmediamodule_media_display', [
            'slug' => $entity->getSlug(),
            'collectionSlug' => $entity->getCollection()->getSlug()
        ]);

        return [
            'mediaType' => $mediaTypeCollection->getMediaTypeFromEntity($entity),
            'entity' => $entity,
            'breadcrumbs' => $entity->getCollection()->getBreadcrumbs($this->get('router'), true),
            'views' => $this->getVar('enableMediaViewCounter', false) ? $entity->getViews() : '-1',
            'hook' => $this->getDisplayHookContent('media', UiHooksCategory::TYPE_DISPLAY_VIEW, $entity->getId(), $hookUrl)
        ];
    }

    private function checkMediaCreationAllowed()
    {
        $qb = $this->securityManager->getCollectionsWithAccessQueryBuilder(
            CollectionPermissionSecurityTree::PERM_LEVEL_ADD_MEDIA
        );
        $qb->setMaxResults(1);

        try {
            $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new AccessDeniedException();
        }
    }

    private function getDefaultEntity(
        Request $request,
        string $type,
        MediaTypeInterface $mediaType,
        int $init,
        $collection,
        $uploadDir
    ) {
        if (!(bool) $init) {
            $entity = $mediaType->getEntityClass();
            $entity = new $entity($this->get('request_stack'), $uploadDir);

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
            $entity->setCollection($this->getDoctrine()->getManager()->find(CollectionEntity::class, $collection));
        }

        return $entity;
    }
}
