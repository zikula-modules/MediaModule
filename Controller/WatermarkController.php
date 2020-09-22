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

use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\ImageWatermarkEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\Repository\WatermarkRepository;
use Cmfcmf\Module\MediaModule\Entity\Watermark\TextWatermarkEntity;
use Cmfcmf\Module\MediaModule\Form\Watermark\ImageWatermarkType;
use Cmfcmf\Module\MediaModule\Form\Watermark\TextWatermarkType;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\OptimisticLockException;
use Gedmo\Uploadable\Uploadable;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * @Route("/watermarks")
 */
class WatermarkController extends AbstractController
{
    private $watermarkRepository;

    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        HookDispatcherInterface $hookDispatcher,
        SecurityManager $securityManager,
        MediaTypeCollection $mediaTypeCollection,
        WatermarkRepository $watermarkRepository
    ) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator, $hookDispatcher, $securityManager, $mediaTypeCollection);
        $this->watermarkRepository = $watermarkRepository;
    }

    /**
     * @Route("/", methods={"GET"})
     * @Template("@CmfcmfMediaModule/Watermark/index.html.twig")
     */
    public function index()
    {
        if (!$this->securityManager->hasPermission('watermark', 'moderate')) {
            throw new AccessDeniedException();
        }

        /** @var AbstractWatermarkEntity[] $entities */
        $entities = $this->watermarkRepository->findAll();

        return ['entities' => $entities];
    }

    /**
     * @Route("/new/{type}", requirements={"type"="image|text"})
     * @Template("@CmfcmfMediaModule/Watermark/edit.html.twig")
     *
     * @param $type
     *
     * @return array|RedirectResponse
     */
    public function create(Request $request, UploadableManager $uploadableManager, string $projectDir, $type)
    {
        if (!$this->securityManager->hasPermission('watermark', 'new')) {
            throw new AccessDeniedException();
        }
        if ('image' === $type) {
            $entity = new ImageWatermarkEntity($this->get('request_stack'), $projectDir . '/public/upload');
            $form = ImageWatermarkType::class;
        } elseif ('text' === $type) {
            $entity = new TextWatermarkEntity($this->get('request_stack'), $projectDir . '/public/upload');
            $form = TextWatermarkType::class;
        } else {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm($form, $entity, ['entity' => $entity]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($entity instanceof Uploadable) {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();
                $uploadableManager->markEntityToUpload($entity, $file);
            }

            $em->persist($entity);
            $em->flush();

            $this->addFlash('status', $this->trans('Watermark created!'));

            return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/edit/{id}")
     * @Template("@CmfcmfMediaModule/Watermark/edit.html.twig")
     *
     * @return array
     */
    public function edit(
        Request $request,
        UploadableManager $uploadableManager,
        CacheManager $imagineCacheManager,
        AbstractWatermarkEntity $entity
    ) {
        if (!$this->securityManager->hasPermission($entity, 'edit')) {
            throw new AccessDeniedException();
        }

        if ($entity instanceof ImageWatermarkEntity) {
            $form = ImageWatermarkType::class;
        } elseif ($entity instanceof TextWatermarkEntity) {
            $form = TextWatermarkType::class;
        } else {
            throw new \LogicException();
        }

        $form = $this->createForm($form, $entity, ['entity' => $entity]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            goto edit_error;
        }
        $em = $this->getDoctrine()->getManager();

        if ($entity instanceof Uploadable) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            if (null !== $file && $file->isValid()) {
                // If the file is invalid, it means that no new file has been selected
                // to replace the already uploaded file.
                $uploadableManager->markEntityToUpload($entity, $file);
            }
        }

        try {
            $em->merge($entity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->trans('Someone else edited the watermark. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        // cleanup existing thumbnails
        $this->watermarkRepository->cleanupThumbs($entity, $imagineCacheManager);

        $this->addFlash('status', $this->trans('Watermark updated!'));

        return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');

        edit_error:

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete/{id}")
     * @Template("@CmfcmfMediaModule/Watermark/delete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function delete(Request $request, CacheManager $imagineCacheManager, AbstractWatermarkEntity $entity)
    {
        if (!$this->securityManager->hasPermission($entity, 'delete')) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('GET')) {
            return ['entity' => $entity];
        }

        $em = $this->getDoctrine()->getManager();

        $this->watermarkRepository->cleanupThumbs($entity, $imagineCacheManager);

        $em->remove($entity);
        $em->flush();

        $this->addFlash('status', $this->trans('Watermark deleted!'));

        return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');
    }
}
