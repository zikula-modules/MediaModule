<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\ImageWatermarkEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\Repository\WatermarkRepository;
use Cmfcmf\Module\MediaModule\Entity\Watermark\TextWatermarkEntity;
use Cmfcmf\Module\MediaModule\Form\Watermark\ImageWatermarkType;
use Cmfcmf\Module\MediaModule\Form\Watermark\TextWatermarkType;
use Doctrine\ORM\OptimisticLockException;
use Gedmo\Uploadable\Uploadable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/watermarks")
 */
class WatermarkController extends AbstractController
{
    /**
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('watermark', 'moderate')) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        /** @var AbstractWatermarkEntity[] $entities */
        $entities = $em->getRepository('CmfcmfMediaModule:Watermark\\AbstractWatermarkEntity')->findAll();

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Route("/new/{type}")
     * @Template(template="CmfcmfMediaModule:Watermark:Edit.html.twig")
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

        if (!in_array($type, ['image', 'text'])) {
            throw new NotFoundHttpException();
        }
        if ($type == 'image') {
            $entity = new ImageWatermarkEntity();
            $form = new ImageWatermarkType();
        } elseif ($type == 'text') {
            $entity = new TextWatermarkEntity();
            $form = new TextWatermarkType();
        } else {
            throw new \LogicException();
        }

        $form = $this->createForm($form, $entity);
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

            return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}")
     * @ParamConverter("entity", class="CmfcmfMediaModule:Watermark\AbstractWatermarkEntity")
     * @Template()
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
            $form = new ImageWatermarkType($entity);
        } elseif ($entity instanceof TextWatermarkEntity) {
            $form = new TextWatermarkType();
        } else {
            throw new \LogicException();
        }

        $form = $this->createForm($form, $entity);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            goto edit_error;
        }
        $em = $this->getDoctrine()->getManager();

        if ($entity instanceof Uploadable) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            if ($file !== null && $file->isValid()) {
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

        // Cleanup existing thumbnails.
        /** @var \SystemPlugin_Imagine_Manager $imagineManager */
        $imagineManager = $this->get('systemplugin.imagine.manager');
        $imagineManager->setModule('CmfcmfMediaModule');

        /** @var WatermarkRepository $repository */
        $repository = $em->getRepository('CmfcmfMediaModule:Watermark\AbstractWatermarkEntity');
        $repository->cleanupThumbs($entity, $imagineManager);

        return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');

        edit_error:

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete/{id}")
     * @ParamConverter("entity", class="CmfcmfMediaModule:Watermark\AbstractWatermarkEntity")
     * @Template()
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

        // Cleanup existing thumbnails.
        /** @var \SystemPlugin_Imagine_Manager $imagineManager */
        $imagineManager = $this->get('systemplugin.imagine.manager');
        $imagineManager->setModule('CmfcmfMediaModule');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('CmfcmfMediaModule:Watermark\AbstractWatermarkEntity');
        $repository->cleanupThumbs($entity, $imagineManager);

        $em->remove($entity);
        $em->flush();

        $this->addFlash('status', $this->__('Watermark deleted!'));

        return $this->redirectToRoute('cmfcmfmediamodule_watermark_index');
    }
}
