<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Security\SecurityTree;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/permissions")
 */
class PermissionController extends AbstractController
{
    /**
     * @Route("/show/{slug}", requirements={"slug" = ".+?"})
     * @ParamConverter("collectionEntity", class="Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity", options={"slug" = "slug"})
     * @Template()
     *
     * @param CollectionEntity $collectionEntity
     *
     * @return array
     */
    public function viewAction(CollectionEntity $collectionEntity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $collectionEntity,
            SecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS
        )) {
            throw new AccessDeniedException();
        }

        $collectionPermissionTypeContainer = $this->get('cmfcmf_media_module.collection_permission.container');

        $em = $this->getDoctrine()->getManager();
        $collectionRepository = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');

        $qb = $collectionRepository->getPathQueryBuilder($collectionEntity);
        $qb->select('p')
            ->add('from', new Expr\From('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity', 'p', null), false)
            ->leftJoin('p.collection', 'node')
            ->orderBy('p.position', 'ASC')
        ;

        /** @var AbstractPermissionEntity[] $entities */
        $entities = $qb->getQuery()->getResult();

        return [
            'entities' => $entities,
            'collection' => $collectionEntity,
            'permissionTypes' => $collectionPermissionTypeContainer->getCollectionPermissions()
        ];
    }

    /**
     * @Route("/new/{type}/{collection}/{afterPermission}", options={"expose"="true"})
     * @Template(template="CmfcmfMediaModule:Permission:edit.html.twig")
     *
     * @param Request $request
     * @param $type
     *
     * @return array|RedirectResponse
     */
    public function newAction(Request $request, $type, CollectionEntity $collection, AbstractPermissionEntity $afterPermission)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $collection,
            SecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS
        )) {
            throw new AccessDeniedException();
        }

        $collectionPermissionTypeContainer = $this->get('cmfcmf_media_module.collection_permission.container');
        try {
            $collectionPermissionType = $collectionPermissionTypeContainer->getCollectionPermission($type);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException();
        }

        $entity = $collectionPermissionType->getEntityClass();
        $entity = new $entity();
        $form = $collectionPermissionType->getFormClass();
        $form = new $form();

        $form = $this->createForm($form, $entity);
        $form->handleRequest($request);

        /** @var AbstractPermissionEntity $entity */
        $entity->setCollection($collection);
        $entity->setPosition($afterPermission->getPosition() + 1);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('status', $this->__('Permission created!'));

            return $this->redirectToRoute('cmfcmfmediamodule_permission_view', [
                'slug' => $collection->getSlug(),
                'highlight' => $entity->getId()
            ]);
        }

        return ['form' => $form->createView(), 'collection' => $collection];
    }

    /**
     * @Route("/edit/{id}")
     * @ParamConverter("entity", class="CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity")
     * @Template()
     *
     * @param Request                  $request
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return array
     */
    public function editAction(Request $request, AbstractPermissionEntity $permissionEntity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $permissionEntity->getCollection(),
            SecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS
        )) {
            throw new AccessDeniedException();
        }
        $collectionPermissionTypeContainer = $this->get('cmfcmf_media_module.collection_permission.container');
        foreach ($collectionPermissionTypeContainer->getCollectionPermissions() as $collectionPermission) {
            if (is_a($permissionEntity, $collectionPermission->getEntityClass(), false)) {
                $form = $collectionPermission->getFormClass();
                $form = new $form();
                break;
            }
        }
        if (!isset($form)) {
            throw new \LogicException();
        }

        $form = $this->createForm($form, $permissionEntity);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            goto edit_error;
        }
        $em = $this->getDoctrine()->getManager();

        try {
            $em->merge($permissionEntity);
            $em->flush();
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->__('Someone else edited the permission. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        $this->addFlash('status', $this->__('Permission updated!'));

        return $this->redirectToRoute('cmfcmfmediamodule_permission_view', [
            'slug' => $permissionEntity->getCollection()->getSlug()
        ]);

        edit_error:

        return ['form' => $form->createView(), 'collection' => $permissionEntity->getCollection()];
    }

    /**
     * @Route("/delete/{id}")
     * @ParamConverter("permissionEntity", class="CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity")
     * @Template()
     *
     * @param Request                  $request
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, AbstractPermissionEntity $permissionEntity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $permissionEntity->getCollection(),
            SecurityTree::PERM_LEVEL_DELETE_COLLECTION)
        ) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('GET')) {
            return ['permission' => $permissionEntity];
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($permissionEntity);
        $em->flush();

        $this->addFlash('status', $this->__('Permission deleted!'));

        return $this->redirectToRoute('cmfcmfmediamodule_permission_view', [
            'slug' => $permissionEntity->getCollection()->getSlug()
        ]);
    }
}
