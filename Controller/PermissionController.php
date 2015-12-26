<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Exception\InvalidPositionException;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\SecurityTree;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Response\PlainResponse;

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

        $qb = $this->getPermissionsOfCollectionAndParentCollectionsQueryBuilder($collectionEntity);
        /** @var AbstractPermissionEntity[] $entities */
        $entities = $qb->getQuery()->getResult();

        return [
            'entities' => $entities,
            'collection' => $collectionEntity,
            'collectionPermissionTypeContainer' => $collectionPermissionTypeContainer
        ];
    }

    /**
     * @Route("/new/{type}/{collection}/{afterPermission}", options={"expose"="true"})
     * @Template(template="CmfcmfMediaModule:Permission:edit.html.twig")
     *
     * @param Request                  $request
     * @param                          $type
     * @param CollectionEntity         $collection
     * @param AbstractPermissionEntity $afterPermission
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

        $permissionTypeContainer = $this->get('cmfcmf_media_module.collection_permission.container');
        try {
            $permissionType = $permissionTypeContainer->getCollectionPermission($type);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException();
        }

        $entity = $permissionType->getEntityClass();
        $entity = new $entity();
        $form = $permissionType->getFormClass();
        $form = new $form();

        $form = $this->createForm($form, $entity);
        $form->handleRequest($request);

        /** @var AbstractPermissionEntity $entity */
        $entity->setCollection($collection);
        $entity->setPosition($afterPermission->getPosition() + 1);

        if ($form->isValid()) {
            $permissionRepository = $this
                ->getDoctrine()
                ->getRepository('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity');
            $permissionRepository->save($entity, true);

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
        $form = $collectionPermissionTypeContainer
            ->getCollectionPermissionFromEntity($permissionEntity)
            ->getFormClass();

        $form = $this->createForm($form, $permissionEntity);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            goto edit_error;
        }

        try {
            $permissionRepository = $this
                ->getDoctrine()
                ->getRepository('CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity');
            $permissionRepository->save($permissionEntity, true);
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
            $collectionPermissionContainer = $this->get(
                'cmfcmf_media_module.collection_permission.container'
            );

            return [
                'permission' => $permissionEntity,
                'collectionPermission' => $collectionPermissionContainer
                    ->getCollectionPermissionFromEntity($permissionEntity)
            ];
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($permissionEntity);
        $em->flush();

        $this->addFlash('status', $this->__('Permission deleted!'));

        return $this->redirectToRoute('cmfcmfmediamodule_permission_view', [
            'slug' => $permissionEntity->getCollection()->getSlug()
        ]);
    }


    /**
     * @Route("/ajax/reorder/{permissionId}/{permissionVersion}/{newIndex}.json",
     *     options={"expose" = true},
     *     requirements={"newIndex"="\d+", "permissionVersion"="\d+", "permissionId"="\d+"}
     * )
     * @ParamConverter("permissionEntity", class="CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity", options={"id" = "permissionId"})
     *
     * @param AbstractPermissionEntity $permissionEntity
     * @param int                      $permissionVersion
     * @param int                      $newIndex
     *
     * @return PlainResponse
     */
    public function reorderAction(
        AbstractPermissionEntity $permissionEntity,
        $permissionVersion,
        $newIndex
    ) {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $permissionEntity->getCollection(),
            SecurityTree::PERM_LEVEL_EDIT_COLLECTION
        )) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }
        $repository = $this->getDoctrine()->getRepository(
            'CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity'
        );

        $permissionEntity->setVersion($permissionVersion);

        $allPermissions = $this->getPermissionsOfCollectionAndParentCollectionsQueryBuilder(
            $permissionEntity->getCollection()
        )->getQuery()->getArrayResult();

        $permissionEntity->setPosition($allPermissions[$newIndex]['position']);

        try {
            $repository->save($permissionEntity, false);
        } catch (OptimisticLockException $e) {
            return new JsonResponse(['error' => $this->__(
                'Someone modified a permission rule. Please reload the page and try again!'
            )], Response::HTTP_FORBIDDEN);
        } catch (InvalidPositionException $e) {
            return new JsonResponse(['error' => $this->__(
                'You cannot move the permission rule to this position!'
            )], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(['newVersion' => $permissionEntity->getVersion()]);
    }

    /**
     * @param CollectionEntity $collectionEntity
     *
     * @return QueryBuilder
     */
    private function getPermissionsOfCollectionAndParentCollectionsQueryBuilder(
        CollectionEntity $collectionEntity
    ) {
        $em = $this->getDoctrine()->getManager();
        $collectionRepository = $em->getRepository('CmfcmfMediaModule:Collection\CollectionEntity');

        $qb = $collectionRepository->getPathQueryBuilder($collectionEntity);
        $qb->select('p')
            ->add(
                'from',
                new Expr\From(
                    'CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity',
                    'p',
                    null
                ),
                false
            )
            ->leftJoin('p.collection', 'node')
            ->orderBy('p.position', 'ASC');

        return $qb;
    }
}
