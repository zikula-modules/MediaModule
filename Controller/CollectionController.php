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

use Cmfcmf\Module\MediaModule\CollectionTemplate\SelectedTemplateFactory;
use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractFileEntity;
use Cmfcmf\Module\MediaModule\Form\Collection\CollectionType;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\MediaType\UploadableMediaTypeInterface;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\CoreBundle\RouteUrl;
use Zikula\Bundle\HookBundle\Category\FormAwareCategory;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class CollectionController extends AbstractController
{
    /**
     * @var CollectionRepository
     */
    private $collectionRepository;

    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        HookDispatcherInterface $hookDispatcher,
        SecurityManager $securityManager,
        MediaTypeCollection $mediaTypeCollection,
        CollectionRepository $collectionRepository
    ) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator, $hookDispatcher, $securityManager, $mediaTypeCollection);
        $this->collectionRepository = $collectionRepository;
    }

    /**
     * @Route("/new/{slug}", requirements={"slug" = ".+"})
     * @Template("@CmfcmfMediaModule/Collection/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function newAction(Request $request, CollectionEntity $parent)
    {
        if (null === $parent) {
            throw new NotFoundHttpException();
        }
        if (!$this->securityManager->hasPermission($parent, CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS)) {
            throw new AccessDeniedException();
        }

        $entity = new CollectionEntity();
        $form = $this->createForm(CollectionType::class, $entity, [
            'parent' => $parent
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->hookValidates('collection', UiHooksCategory::TYPE_VALIDATE_EDIT)) {
                if (!$this->securityManager->hasPermission($entity->getParent(), CollectionPermissionSecurityTree::PERM_LEVEL_ADD_SUB_COLLECTIONS)) {
                    throw new AccessDeniedException($this->trans('You don\'t have permission to add a sub-collection to the selected parent collection.'));
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $hookUrl = new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);
                $this->applyFormAwareProcessHook($form, 'collection', FormAwareCategory::TYPE_PROCESS_EDIT, $entity, $hookUrl);
                $this->applyProcessHook('collection', UiHooksCategory::TYPE_PROCESS_EDIT, $entity->getId(), $hookUrl);

                return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);
            }
            $this->hookValidationError($form);
        }

        $formHook = $this->applyFormAwareDisplayHook($form, 'collections', FormAwareCategory::TYPE_EDIT);

        return [
            'form' => $form->createView(),
            'hook' => $this->getDisplayHookContent('collections', UiHooksCategory::TYPE_FORM_EDIT),
            'formHookTemplates' => $formHook->getTemplates()
        ];
    }

    /**
     * @Route("/edit/{slug}", requirements={"slug" = ".+"})
     * @Template("@CmfcmfMediaModule/Collection/edit.html.twig")
     *
     * @return array
     */
    public function editAction(Request $request, CollectionEntity $entity)
    {
        if (!$this->securityManager->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_COLLECTION)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(CollectionType::class, $entity, [
            'parent' => $entity->getParent()
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            goto edit_error;
        }

        if (!$this->hookValidates('collection', UiHooksCategory::TYPE_VALIDATE_EDIT)) {
            $this->hookValidationError($form);
            goto edit_error;
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->merge($entity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->trans('Someone else edited the collection. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        $hookUrl = new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);
        $this->applyFormAwareProcessHook($form, 'collection', FormAwareCategory::TYPE_PROCESS_EDIT, $entity, $hookUrl);
        $this->applyProcessHook('collection', UiHooksCategory::TYPE_PROCESS_EDIT, $entity->getId(), $hookUrl);

        return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);

        edit_error:

        $hookUrl = new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $entity->getSlug()]);
        $formHook = $this->applyFormAwareDisplayHook($form, 'collections', FormAwareCategory::TYPE_EDIT, $hookUrl);

        return [
            'form' => $form->createView(),
            'hook' => $this->getDisplayHookContent('collections', UiHooksCategory::TYPE_FORM_EDIT, $entity->getId(), $hookUrl),
            'formHookTemplates' => $formHook->getTemplates()
        ];
    }

    /**
     * @Route("/download/{slug}.zip", requirements={"slug"=".+"})
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(CollectionEntity $entity, string $cacheDir)
    {
        if (!$this->securityManager->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_DOWNLOAD_COLLECTION)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();

        $cacheDirectory = $cacheDir . '/CmfCmfMediaModule/';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($cacheDirectory)) {
            $filesystem->mkdir($cacheDirectory, 0777);
        }
        if (!$filesystem->exists($cacheDirectory)) {
            $cacheDirectory = sys_get_temp_dir();
        }

        $path = $cacheDirectory . '/' . uniqid(time(), true) . '.zip';

        $zip = new \ZipArchive();
        if (true !== $zip->open($path, \ZipArchive::CREATE)) {
            throw new ServiceUnavailableHttpException('Could not create zip archive!');
        }
        $mediaTypeCollection = $this->mediaTypeCollection;

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
                    $filename = "${originalFilename} (${i})" . (empty($originalFileExtension) ?: ".${originalFileExtension}");
                }
                $zip->addFile($mediaType->getOriginalWithWatermark($media, 'path', false), $filename);
                $hasContent = true;

                $media->setDownloads($media->getDownloads() + 1);
                $em->merge($media);
            }
        }
        if (!$hasContent) {
            $zip->addFromString('Empty Collection.txt', $this->trans('Sorry, the collection appears to be empty or does not have any downloadable files.'));
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
     * @return PlainResponse
     */
    public function reorderAction(Request $request)
    {
        $id = $request->query->get('id');
        $oldPosition = $request->query->get('old-position');
        $newPosition = $request->query->get('new-position');
        $diff = $newPosition - $oldPosition;

        $entity = $this->collectionRepository->find($id);

        if (!$this->securityManager->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_COLLECTION)) {
            throw new AccessDeniedException();
        }

        if ($diff > 0) {
            $result = $this->collectionRepository->moveDown($entity, $diff);
        } else {
            $result = $this->collectionRepository->moveUp($entity, $diff * -1);
        }
        if (!$result) {
            throw new \RuntimeException();
        }

        return new PlainResponse();
    }

    /**
     * @Route("", methods={"GET"})
     *
     * @return RedirectResponse
     */
    public function displayRootAction()
    {
        $rootCollection = $this->collectionRepository->getRootNode();

        if (!$this->securityManager->hasPermission($rootCollection, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        return $this->redirectToRoute('cmfcmfmediamodule_collection_display', ['slug' => $rootCollection->getSlug()]);
    }

    /**
     * @Route("/show-by-id/{id}", options={"expose" = true})
     *
     * @return RedirectResponse
     */
    public function displayByIdAction(CollectionEntity $entity)
    {
        if (!$this->securityManager->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        return $this->redirectToRoute(
            'cmfcmfmediamodule_collection_display',
            ['slug' => $entity->getSlug()]
        );
    }

    /**
     * @Route("/{slug}", methods={"GET"}, requirements={"slug"=".*[^/]"}, options={"expose" = true})
     * @Template("@CmfcmfMediaModule/Collection/display.html.twig")
     */
    public function displayAction(
        Request $request,
        CollectionEntity $entity,
        SelectedTemplateFactory $selectedTemplateFactory
    ): Response {
        if (!$this->securityManager->hasPermission($entity, CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        if (null !== $entity->getDefaultTemplate()) {
            $defaultTemplate = $entity->getDefaultTemplate();
        } else {
            $defaultTemplate = $this->getVar('defaultCollectionTemplate');
        }
        $template = $request->query->get('template', $defaultTemplate);

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
            $qb->update(CollectionEntity::class, 'c')
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
        $templateVars['hook'] = $this->getDisplayHookContent('collections', UiHooksCategory::TYPE_DISPLAY_VIEW, $entity->getId(), $hookUrl);
        $templateVars['renderRaw'] = $isHook = $request->query->get('isHook', false);

        $templateVars['content'] = $selectedTemplate->getTemplate()->render(
            $entity,
            $this->mediaTypeCollection,
            !$isHook,
            $selectedTemplate->getOptions()
        );

        return $this->render('@CmfcmfMediaModule/Collection/display.html.twig', $templateVars);
    }
}
