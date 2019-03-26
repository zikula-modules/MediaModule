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
use Doctrine\ORM\OptimisticLockException;
use Gedmo\Uploadable\Uploadable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/watermarks")
 */
class WatermarkController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"})
     * @Template("CmfcmfMediaModule:Watermark:index.html.twig")
     */
    public function indexAction()
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('watermark', 'moderate')) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        /** @var AbstractWatermarkEntity[] $entities */
        $entities = $em->getRepository('CmfcmfMediaModule:Watermark\\AbstractWatermarkEntity')->findAll();

        return ['entities' => $entities];
    }

    /**
     * @Route("/new/{type}", requirements={"type"="image|text"})
     * @Template("CmfcmfMediaModule:Watermark:edit.html.twig")
     *
     * @param Request $request
     * @param $type
     *
     * @return array|RedirectResponse
     */
    public function newAction(Request $request, $type)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('watermark', 'new')) {
            throw new AccessDeniedException();
        }
        $dataDirectory = $this->get('service_container')->getParameter('datadir');
        if ('image' === $type) {
            $entity = new ImageWatermarkEntity($this->get('request_stack'), $dataDirectory);
            $form = ImageWatermarkType::class;
        } elseif ('text' === $type) {
            $entity = new TextWatermarkEntity($this->get('request_stack'), $dataDirectory);
            $form = TextWatermarkType::class;
        } else {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm($form, $entity, ['entity' => $entity]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($entity instanceof Uploadable) {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();
                $manager = $this->get('stof_doctrine_extensions.uploadable.manager');
                $manager->markEntityToUpload($entity, $file);
            }

            $em->persist($entity);
            $em->flush();

            $this->addFlash('status', $this->__('Watermark created!'));

            return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/edit/{id}")
     * @Template("CmfcmfMediaModule:Watermark:edit.html.twig")
     *
     * @param Request                 $request
     * @param AbstractWatermarkEntity $entity
     *
     * @return array
     */
    public function editAction(Request $request, AbstractWatermarkEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, 'edit')) {
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

        if (!$form->isValid()) {
            goto edit_error;
        }
        $em = $this->getDoctrine()->getManager();

        if ($entity instanceof Uploadable) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            if (null !== $file && $file->isValid()) {
                // If the file is invalid, it means that no new file has been selected
                // to replace the already uploaded file.
                $manager = $this->get('stof_doctrine_extensions.uploadable.manager');
                $manager->markEntityToUpload($entity, $file);
            }
        }

        try {
            $em->merge($entity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->__('Someone else edited the watermark. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        // Cleanup existing thumbnails
        /** @var Liip\ImagineBundle\Imagine\Cache\CacheManager $imagineCacheManager */
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');

        /** @var WatermarkRepository $repository */
        $repository = $em->getRepository('CmfcmfMediaModule:Watermark\AbstractWatermarkEntity');
        $repository->cleanupThumbs($entity, $imagineCacheManager);

        $this->addFlash('status', $this->__('Watermark updated!'));

        return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');

        edit_error:

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/delete/{id}")
     * @Template("CmfcmfMediaModule:Watermark:delete.html.twig")
     *
     * @param Request                 $request
     * @param AbstractWatermarkEntity $entity
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, AbstractWatermarkEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, 'delete')) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('GET')) {
            return ['entity' => $entity];
        }

        $em = $this->getDoctrine()->getManager();

        // Cleanup existing thumbnails
        /** @var Liip\ImagineBundle\Imagine\Cache\CacheManager $imagineCacheManager */
        $imagineCacheManager = $this->get('liip_imagine.cache.manager');

        /** @var WatermarkRepository $repository */
        $repository = $em->getRepository('CmfcmfMediaModule:Watermark\AbstractWatermarkEntity');
        $repository->cleanupThumbs($entity, $imagineCacheManager);

        $em->remove($entity);
        $em->flush();

        $this->addFlash('status', $this->__('Watermark deleted!'));

        return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');
    }
}
