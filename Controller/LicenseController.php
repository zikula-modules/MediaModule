<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\Form\License\LicenseType;
use Doctrine\ORM\OptimisticLockException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/licenses")
 */
class LicenseController extends AbstractController
{
    /**
     * @Route("/")
     * @Method("GET")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('license', 'moderate')) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        /** @var LicenseEntity[] $entities */
        $entities = $em->getRepository('CmfcmfMediaModule:License\LicenseEntity')->findBy([], ['id' => 'ASC']);

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Route("/new")
     * @Template(template="CmfcmfMediaModule:License:Edit.html.twig")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function newAction(Request $request)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('license', 'add')) {
            throw new AccessDeniedException();
        }

        $entity = new LicenseEntity(null);
        $form = new LicenseType(false);

        $form = $this->createForm($form, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('cmfcmfmediamodule_license_index');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}")
     * @ParamConverter("entity", class="CmfcmfMediaModule:License\LicenseEntity")
     * @Template()
     *
     * @param Request       $request
     * @param LicenseEntity $entity
     *
     * @return array
     */
    public function editAction(Request $request, LicenseEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, 'edit')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new LicenseType(true), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->merge($entity);
                $em->flush();

                return $this->redirectToRoute('cmfcmfmediamodule_license_index');
            } catch (OptimisticLockException $e) {
                $form->addError(new FormError($this->__('Someone else edited the collection. Please either cancel editing or force reload the page.')));
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete/{id}")
     * @ParamConverter("entity", class="CmfcmfMediaModule:License\LicenseEntity")
     * @Template()
     *
     * @param Request       $request
     * @param LicenseEntity $entity
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, LicenseEntity $entity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission($entity, 'delete')) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('GET')) {
            return ['entity' => $entity];
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->addFlash('status', $this->__('License deleted!'));

        return $this->redirectToRoute('cmfcmfmediamodule_license_index');
    }
}
