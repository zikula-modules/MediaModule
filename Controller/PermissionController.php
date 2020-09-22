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
use Cmfcmf\Module\MediaModule\Entity\Collection\Permission\AbstractPermissionEntity;
use Cmfcmf\Module\MediaModule\Entity\Collection\Repository\CollectionRepository;
use Cmfcmf\Module\MediaModule\Exception\InvalidPositionException;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionContainer;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
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
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

/**
 * @Route("/permissions")
 */
class PermissionController extends AbstractController
{
    /**
     * @var CollectionPermissionContainer
     */
    private $collectionPermissionTypeContainer;

    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        HookDispatcherInterface $hookDispatcher,
        SecurityManager $securityManager,
        MediaTypeCollection $mediaTypeCollection,
        CollectionPermissionContainer $collectionPermissionTypeContainer
    ) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator, $hookDispatcher, $securityManager, $mediaTypeCollection);
        $this->collectionPermissionTypeContainer = $collectionPermissionTypeContainer;
    }

    /**
     * @Route("/show/{slug}", requirements={"slug" = ".+?"})
     * @Template("@CmfcmfMediaModule/Permission/view.html.twig")
     *
     * @return array
     */
    public function view(
        Request $request,
        CollectionEntity $collectionEntity,
        CollectionRepository $collectionRepository,
        CurrentUserApiInterface $currentUserApi
    ) {
        if (!$this->securityManager->hasPermission(
            $collectionEntity,
            CollectionPermissionSecurityTree::PERM_LEVEL_EDIT_COLLECTION
        )) {
            throw new AccessDeniedException();
        }

        $qb = $this->getPermissionsOfCollectionAndParentCollectionsQueryBuilder($collectionEntity, $collectionRepository);
        /** @var AbstractPermissionEntity[] $entities */
        $entities = $qb->getQuery()->getResult();

        return [
            'entities' => $entities,
            'collection' => $collectionEntity,
            'collectionPermissionTypeContainer' => $this->collectionPermissionTypeContainer,
            'userId' => $currentUserApi->get('uid'),
            'highlight' => $request->query->get('highlight', null)
        ];
    }

    /**
     * @Route("/new/{type}/{collection}/{afterPermission}", options={"expose"="true"})
     * @Template("@CmfcmfMediaModule/Permission/edit.html.twig")
     *
     * @param string                   $type
     *
     * @return array|RedirectResponse
     */
    public function create(
        Request $request,
        $type,
        CollectionEntity $collection,
        AbstractPermissionEntity $afterPermission,
        string $projectDir
    ) {
        $permissionLevel = $this->getPermissionLevelOrException($collection);

        try {
            $permissionType = $this->collectionPermissionTypeContainer->getCollectionPermission($type);
        } catch (\DomainException $e) {
            throw new NotFoundHttpException();
        }

        $entity = $permissionType->getEntityClass();
        $entity = new $entity($this->get('request_stack'), $projectDir . '/public/uploads');

        $formClass = $permissionType->getFormClass();
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm($formClass, $entity, [
            'collection' => $collection,
            'permissionLevel' => $permissionLevel
        ]);
        $form->handleRequest($request);

        /** @var AbstractPermissionEntity $entity */
        $entity->setCollection($collection);
        $entity->setPosition($afterPermission->getPosition() + 1);
        if (CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS === $permissionLevel) {
            $entity->setGoOn(true);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $permissionRepository = $this
                ->getDoctrine()
                ->getRepository(AbstractPermissionEntity::class);
            $permissionRepository->save($entity, true);

            $this->addFlash('status', $this->trans('Permission created!'));

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
     * @Template("@CmfcmfMediaModule/Permission/edit.html.twig")
     *
     * @return array
     */
    public function edit(Request $request, AbstractPermissionEntity $permissionEntity)
    {
        $permissionLevel = $this->getPermissionLevelOrException($permissionEntity->getCollection(), $permissionEntity);

        $formClass = $this->collectionPermissionTypeContainer
            ->getCollectionPermissionFromEntity($permissionEntity)
            ->getFormClass();

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm($formClass, $permissionEntity, [
            'collection' => $permissionEntity->getCollection(),
            'permissionLevel' => $permissionLevel
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            goto edit_error;
        }

        try {
            $permissionRepository = $this
                ->getDoctrine()
                ->getRepository(AbstractPermissionEntity::class);
            $permissionRepository->save($permissionEntity, true);
        } catch (OptimisticLockException $e) {
            $form->addError(new FormError($this->trans('Someone else edited the permission. Please either cancel editing or force reload the page.')));
            goto edit_error;
        }

        $this->addFlash('status', $this->trans('Permission updated!'));

        return $this->redirectToRoute('cmfcmfmediamodule_permission_view', [
            'slug' => $permissionEntity->getCollection()->getSlug()
        ]);

        edit_error:

        return ['form' => $form->createView(), 'collection' => $permissionEntity->getCollection()];
    }

    /**
     * @Route("/delete/{id}")
     * @Template("@CmfcmfMediaModule/Permission/delete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function delete(Request $request, AbstractPermissionEntity $permissionEntity)
    {
        $this->getPermissionLevelOrException($permissionEntity->getCollection(), $permissionEntity);

        if ($request->isMethod('GET')) {
            return [
                'permission' => $permissionEntity,
                'collectionPermission' => $this->collectionPermissionTypeContainer
                    ->getCollectionPermissionFromEntity($permissionEntity)
            ];
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($permissionEntity);
        $em->flush();

        $this->addFlash('status', $this->trans('Permission deleted!'));

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
     * @param int                      $permissionVersion
     * @param int                      $newIndex
     *
     * @return PlainResponse
     */
    public function reorder(
        AbstractPermissionEntity $permissionEntity,
        CollectionRepository $collectionRepository,
        $permissionVersion,
        $newIndex
    ) {
        try {
            $this->getPermissionLevelOrException($permissionEntity->getCollection(), $permissionEntity);
        } catch (AccessDeniedException $e) {
            return $this->json([], JsonResponse::HTTP_FORBIDDEN);
        }

        $repository = $this->getDoctrine()->getRepository(AbstractPermissionEntity::class);

        $permissionEntity->setVersion($permissionVersion);

        $allPermissions = $this->getPermissionsOfCollectionAndParentCollectionsQueryBuilder(
            $permissionEntity->getCollection(),
            $collectionRepository
        )->getQuery()->getArrayResult();

        $permissionEntity->setPosition($allPermissions[$newIndex]['position']);

        try {
            $repository->save($permissionEntity, false);
        } catch (OptimisticLockException $e) {
            return $this->json(['error' => $this->trans(
                'Someone modified a permission rule. Please reload the page and try again!'
            )], Response::HTTP_FORBIDDEN);
        } catch (InvalidPositionException $e) {
            return $this->json(['error' => $this->trans(
                'You cannot move the permission rule to this position!'
            )], Response::HTTP_FORBIDDEN);
        }

        return $this->json(['newVersion' => $permissionEntity->getVersion()]);
    }

    /**
     * @return QueryBuilder
     */
    private function getPermissionsOfCollectionAndParentCollectionsQueryBuilder(
        CollectionEntity $collectionEntity,
        CollectionRepository $collectionRepository
    ) {
        $qb = $collectionRepository->getPathQueryBuilder($collectionEntity);
        $qb->select('p')
            ->add(
                'from',
                new Expr\From(
                    AbstractPermissionEntity::class,
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
     * @param AbstractPermissionEntity $permissionEntity
     *
     * @return string
     */
    private function getPermissionLevelOrException(
        CollectionEntity $collectionEntity,
        CurrentUserApiInterface $currentUserApi,
        AbstractPermissionEntity $permissionEntity = null
    ) {
        if ($this->securityManager->hasPermission($collectionEntity, CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS)) {
            return CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS;
        }
        if (null === $permissionEntity) {
            throw new AccessDeniedException();
        }
        if ($permissionEntity->getCreatedBy()->getUid() === $currentUserApi->get('uid')) {
            if ($this->securityManager->hasPermission($collectionEntity, CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS)) {
                return CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS;
            }
        }
        throw new AccessDeniedException();
    }
}
