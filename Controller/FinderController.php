<?php

namespace Cmfcmf\Module\MediaModule\Controller;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\SecurityTree;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/finder")
 */
class FinderController extends AbstractController
{
    /**
     * @Route("/choose", options={"expose" = true})
     * @Template()
     */
    public function chooseMethodAction()
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('media', 'display')) {
            throw new AccessDeniedException();
        }
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('collection', 'display')) {
            // @todo Use new permissions.
            throw new AccessDeniedException();
        }

        return [];
    }

    /**
     * @Route("/popup/choose/collection", options={"expose" = true})
     * @Template()
     */
    public function popupChooseCollectionsAction()
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('collection', 'display')) {
            // @todo Use new permissions.
            throw new AccessDeniedException();
        }

        return [];
    }

    /**
     * @Route("/popup/choose/media", options={"expose" = true})
     * @Template()
     */
    public function popupChooseMediaAction()
    {
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('media', 'display')) {
            throw new AccessDeniedException();
        }
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('collection', 'display')) {
            // @todo Use new permissions.
            throw new AccessDeniedException();
        }

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
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('media', 'display')) {
            throw new AccessDeniedException();
        }
        if (!$this->get('cmfcmf_media_module.security_manager')->hasPermission('collection', 'display')) {
            throw new AccessDeniedException();
        }
        // @todo Use new permissions.

        $q = $request->query->get('q');

        /** @var QueryBuilder $qb */
        $qb = $this->getDoctrine()->getRepository('CmfcmfMediaModule:Media\AbstractMediaEntity')->createQueryBuilder('m');

        $mediaResults = $qb
            ->where($qb->expr()->like('m.title', ':q'))
            ->setParameter('q', "%$q%")
            ->getQuery()
            ->execute()
        ;

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');
        $mediaResults = array_filter($mediaResults, function (AbstractMediaEntity $entity) use ($mediaTypeCollection) {
            return $mediaTypeCollection->getMediaTypeFromEntity($entity)->isEmbeddable();
        });
        $mediaResults = array_map(function (AbstractMediaEntity $entity) use ($mediaTypeCollection) {
            return $entity->toArrayForFinder($mediaTypeCollection);
        }, $mediaResults);

        /** @var QueryBuilder $qb */
        $qb = $this->getDoctrine()->getRepository('CmfcmfMediaModule:Collection\CollectionEntity')->createQueryBuilder('c');

        $collectionResults = $qb
            ->where($qb->expr()->like('c.title', ':q'))
            ->setParameter('q', "%$q%")
            ->getQuery()
            ->execute()
        ;
        $collectionResults = array_map(function (CollectionEntity $entity) use ($mediaTypeCollection) {
            return $entity->toArrayForFinder($mediaTypeCollection);
        }, $collectionResults);

        return new JsonResponse([
            'media' => $mediaResults,
            'collections' => $collectionResults
        ]);
    }

    /**
     * @Route("/ajax/get-collections/{parentId}/{hookedObjectId}", options={"expose"=true}, requirements={"hookedObjectId" = "\d+"})
     *
     * @param int $parentId
     * @param int $hookedObjectId
     *
     * @return JsonResponse
     */
    public function getCollectionsAction($parentId, $hookedObjectId = null)
    {
        $securityManager = $this->get('cmfcmf_media_module.security_manager');
        if (!$securityManager->hasPermission('collection', 'view')) {
            throw new AccessDeniedException();
        }

        $mediaTypeCollection = $this->get('cmfcmf_media_module.media_type_collection');

        $em = $this->getDoctrine()->getManager();
        if ($parentId == '#') {
            $parentId = null;
        }
        if ($hookedObjectId != null) {
            $hookedObjectEntity = $em->find('CmfcmfMediaModule:HookedObject\HookedObjectEntity', $hookedObjectId);
        } else {
            $hookedObjectEntity = null;
        }

        $qb = $securityManager->getCollectionsWithAccessQueryBuilder(SecurityTree::PERM_LEVEL_OVERVIEW);
        $collections = $qb
            ->andWhere($qb->expr()->eq('c.parent', ':parentId'))
            ->setParameter('parentId', $parentId)
            ->getQuery()
            ->execute()
        ;
        $collections = array_map(function (CollectionEntity $collection) use ($mediaTypeCollection, $hookedObjectEntity) {
            return $collection->toArrayForJsTree($mediaTypeCollection, $hookedObjectEntity, true);
        }, $collections);

        return new JsonResponse($collections);
    }
}
