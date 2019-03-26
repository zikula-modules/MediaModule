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
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/finder")
 */
class FinderController extends AbstractController
{
    /**
     * @Route("/choose", options={"expose" = true})
     * @Template("CmfcmfMediaModule:Finder:chooseMethod.html.twig")
     */
    public function chooseMethodAction()
    {
        return [];
    }

    /**
     * @Route("/popup/choose/collection", options={"expose" = true})
     * @Template("CmfcmfMediaModule:Finder:popupChooseCollections.html.twig")
     */
    public function popupChooseCollectionsAction()
    {
        return [];
    }

    /**
     * @Route("/popup/choose/media", options={"expose" = true})
     * @Template("CmfcmfMediaModule:Finder:popupChooseMedia.html.twig")
     */
    public function popupChooseMediaAction()
    {
        return [];
    }

    /**
     * @Route("/ajax/find", options={"expose" = true})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajaxFindAction(Request $request)
    {
        $q = trim($request->query->get('q'));
        $securityManager = $this->get('cmfcmf_media_module.security_manager');

        $qb = $securityManager
            ->getMediaWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS);
        $mediaResults = $qb
            ->andWhere($qb->expr()->like('m.title', ':q'))
            ->setParameter('q', "%${q}%")
            ->getQuery()
            ->execute();

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');
        $mediaResults = array_filter($mediaResults, function (AbstractMediaEntity $entity) use ($mediaTypeCollection) {
            return $mediaTypeCollection->getMediaTypeFromEntity($entity)->isEmbeddable();
        });
        $mediaResults = array_map(function (AbstractMediaEntity $entity) use ($mediaTypeCollection) {
            return $entity->toArrayForFinder($mediaTypeCollection);
        }, $mediaResults);

        $qb = $securityManager
            ->getCollectionsWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS);
        $collectionResults = $qb
            ->andWhere($qb->expr()->like('c.title', ':q'))
            ->setParameter('q', "%${q}%")
            ->getQuery()
            ->execute();

        $collectionResults = array_map(function (CollectionEntity $entity) use ($mediaTypeCollection) {
            return $entity->toArrayForFinder($mediaTypeCollection);
        }, $collectionResults);

        return $this->json([
            'media' => $mediaResults,
            'collections' => $collectionResults
        ]);
    }

    /**
     * @Route("/ajax/get-collections/{parentId}/{hookedObjectId}", options={"expose"=true}, requirements={"hookedObjectId" = "\d+", "parentId" = "\d+|\#"})
     *
     * @param int $parentId
     * @param int $hookedObjectId
     *
     * @return JsonResponse
     */
    public function getCollectionsAction($parentId, $hookedObjectId = null)
    {
        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        if (null !== $hookedObjectId) {
            $em = $this->getDoctrine()->getManager();
            $hookedObjectEntity = $em
                ->find('CmfcmfMediaModule:HookedObject\HookedObjectEntity', $hookedObjectId);
        } else {
            $hookedObjectEntity = null;
        }

        $qb = $securityManager
            ->getCollectionsWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW);
        if ('#' === $parentId) {
            $qb->andWhere($qb->expr()->isNull('c.parent'));
        } else {
            $qb->andWhere($qb->expr()->eq('c.parent', ':parentId'))
                ->setParameter('parentId', $parentId);
        }
        $collections = $qb->getQuery()->execute();

        $collections = array_map(function (CollectionEntity $collection) use ($mediaTypeCollection, $hookedObjectEntity) {
            return $collection->toArrayForJsTree($mediaTypeCollection, $hookedObjectEntity);
        }, $collections);

        return $this->json($collections);
    }
}
