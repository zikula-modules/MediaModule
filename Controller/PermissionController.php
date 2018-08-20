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
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Exception\InvalidPositionException;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
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
     * @Template("CmfcmfMediaModule:Permission:view.html.twig")
     *
     * @param Request          $request
     * @param CollectionEntity $collectionEntity
     *
     * @return array
     */
    public function viewAction(Request $request, CollectionEntity $collectionEntity)
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission(
            $collectionEntity,
            CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_COLLECTION
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
            'collectionPermissionTypeContainer' => $collectionPermissionTypeContainer,
            'userId' => $this->get('zikula_users_module.current_user')->get('uid'),
            'highlight' => $request->query->get('highlight', null)
        ];
    }

    /**
     * @Route("/new/{type}/{collection}/{afterPermission}", options={"expose"="true"})
     * @Template("CmfcmfMediaModule:Permission:edit.html.twig")
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
        $permissionLevel = $this->getPermissionLevelOrException($collection);

        $permissionTypeContainer = $this->get('cmfcmf_media_module.collection_permission.container');
        try {
            $permissionType = $permissionTypeContainer->getCollectionPermission($type);
        } catch (\DomainException $e) {
            throw new NotFoundHttpException();
        }

        $dataDirectory = $this->get('service_container')->getParameter('datadir');

        $entity = $permissionType->getEntityClass();
        $entity = new $entity($dataDirectory);

        $form = $permissionType->getFormClass();
        /** @var AbstractType $form */
        $form = new $form(
            $collection,
            $this->get('cmfcmf_media_module.security_manager'),
            $permissionLevel,
            $this->get('zikula_groups_module.group_repository'),
            $this->get('zikula_users_module.user_repository')
        );
        $form->setTranslator($this->get('translator'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm($form, $entity);
        $form->handleRequest($request);

        /** @var AbstractPermissionEntity $entity */
        $entity->setCollection($collection);
        $entity->setPosition($afterPermission->getPosition() + 1);
        if ($permissionLevel == CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS) {
            $entity->setGoOn(true);
        }

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

        return [
            'form' => $form->createView(),
            'collection' => $collection
        ];
    }

    /**
     * @Route("/edit/{id}")
     * @ParamConverter("entity", class="CmfcmfMediaModule:Collection\Permission\AbstractPermissionEntity")
     * @Template("CmfcmfMediaModule:Permission:edit.html.twig")
     *
     * @param Request                  $request
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return array
     */
    public function editAction(Request $request, AbstractPermissionEntity $permissionEntity)
    {
        $permissionLevel = $this->getPermissionLevelOrException($permissionEntity->getCollection(), $permissionEntity);

        $collectionPermissionTypeContainer = $this->get('cmfcmf_media_module.collection_permission.container');
        $form = $collectionPermissionTypeContainer
            ->getCollectionPermissionFromEntity($permissionEntity)
            ->getFormClass();

        /** @var AbstractType $form */
        $form = new $form(
            $permissionEntity->getCollection(),
            $this->get('cmfcmf_media_module.security_manager'),
            $permissionLevel
        );
        $form->setTranslator($this->get('translator'));
        /** @var \Symfony\Component\Form\Form $form */
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
     * @Template("CmfcmfMediaModule:Permission:delete.html.twig")
     *
     * @param Request                  $request
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, AbstractPermissionEntity $permissionEntity)
    {
        $this->getPermissionLevelOrException($permissionEntity->getCollection(), $permissionEntity);

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
        try {
            $this->getPermissionLevelOrException($permissionEntity->getCollection(), $permissionEntity);
        } catch (AccessDeniedException $e) {
            return $this->json([], JsonResponse::HTTP_FORBIDDEN);
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
            return $this->json(['error' => $this->__(
                'Someone modified a permission rule. Please reload the page and try again!'
            )], Response::HTTP_FORBIDDEN);
        } catch (InvalidPositionException $e) {
            return $this->json(['error' => $this->__(
                'You cannot move the permission rule to this position!'
            )], Response::HTTP_FORBIDDEN);
        }

        return $this->json(['newVersion' => $permissionEntity->getVersion()]);
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

    /**
     * @param CollectionEntity         $collectionEntity
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return string
     */
    private function getPermissionLevelOrException(
        CollectionEntity $collectionEntity,
        AbstractPermissionEntity $permissionEntity = null
    ) {
        $securityManager = $this->get('cmfcmf_media_module.security_manager');

        if ($securityManager->hasPermission($collectionEntity, CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS)) {
            return CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS;
        }
        if (null === $permissionEntity) {
            throw new AccessDeniedException();
        }
        if ($permissionEntity->getCreatedBy()->getUid() == $this->get('zikula_users_module.current_user')->get('uid')) {
            if ($securityManager->hasPermission($collectionEntity, CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS)) {
                return CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS;
            }
        }
        throw new AccessDeniedException();
    }
}
