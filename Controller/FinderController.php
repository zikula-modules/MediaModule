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
     * @Template("@CmfcmfMediaModule/Finder/chooseMethod.html.twig")
     */
    public function chooseMethod()
    {
        return [];
    }

    /**
     * @Route("/popup/choose/collection", options={"expose" = true})
     * @Template("@CmfcmfMediaModule/Finder/popupChooseCollections.html.twig")
     */
    public function popupChooseCollections()
    {
        return [];
    }

    /**
     * @Route("/popup/choose/media", options={"expose" = true})
     * @Template("@CmfcmfMediaModule/Finder/popupChooseMedia.html.twig")
     */
    public function popupChooseMedia()
    {
        return [];
    }

    /**
     * @Route("/ajax/find", options={"expose" = true})
     *
     * @return JsonResponse
     */
    public function ajaxFind(Request $request)
    {
        $q = trim($request->query->get('q'));

        $qb = $this->securityManager
            ->getMediaWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS);
        $mediaResults = $qb
            ->andWhere($qb->expr()->like('m.title', ':q'))
            ->setParameter('q', "%${q}%")
            ->getQuery()
            ->execute();

        $mediaTypeCollection = $this->mediaTypeCollection;
        $mediaResults = array_filter($mediaResults, function (AbstractMediaEntity $entity) use ($mediaTypeCollection) {
            return $mediaTypeCollection->getMediaTypeFromEntity($entity)->isEmbeddable();
        });
        $mediaResults = array_map(function (AbstractMediaEntity $entity) use ($mediaTypeCollection) {
            return $entity->toArrayForFinder($mediaTypeCollection);
        }, $mediaResults);

        $qb = $this->securityManager
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
    public function getCollections($parentId, $hookedObjectId = null)
    {
        $mediaTypeCollection = $this->mediaTypeCollection;

        if (null !== $hookedObjectId) {
            $em = $this->getDoctrine()->getManager();
            $hookedObjectEntity = $em
                ->find('CmfcmfMediaModule:HookedObject\HookedObjectEntity', $hookedObjectId);
        } else {
            $hookedObjectEntity = null;
        }

        $qb = $this->securityManager
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
