<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Helper;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Core\RouteUrl;
use Zikula\SearchModule\SearchableInterface;

class SearchHelper implements SearchableInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @param RequestStack    $requestStack
     * @param SecurityManager $securityManager
     */
    public function __construct(
        RequestStack $requestStack,
        SecurityManager $securityManager
    ) {
        $this->requestStack = $requestStack;
        $this->securityManager = $securityManager;
    }

    public function amendForm(FormBuilderInterface $builder)
    {
        // nothing
    }

    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        $results = [];
        $sessionId = $this->requestStack->getCurrentRequest()->getSession()->getId();

        $qb = $this->securityManager->getCollectionsWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW);
        $where = $this->formatWhere($qb, $words, ['c.title'], $searchType);
        $qb->andWhere($where);
        /** @var CollectionEntity[] $collections */
        $collections = $qb->getQuery()->execute();

        foreach ($collections as $collection) {
            $results[] = [
                'title' => $collection->getTitle(),
                'text' => $collection->getDescription(),
                'module' => 'CmfcmfMediaModule',
                'created' => $collection->getCreatedDate(),
                'sesid' => $sessionId,
                'url' => new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $collection->getSlug()])
            ];
        }

        $qb = $this->securityManager->getMediaWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS);
        $where = $this->formatWhere($qb, $words, ['m.title'], $searchType);
        $qb->andWhere($where);
        /** @var AbstractMediaEntity[] $media */
        $media = $qb->getQuery()->execute();

        foreach ($media as $medium) {
            $results[] = [
                'title' => $medium->getTitle(),
                'text' => $medium->getDescription(),
                'module' => 'CmfcmfMediaModule',
                'created' => $medium->getCreatedDate(),
                'sesid' => $sessionId,
                'url' => new RouteUrl('cmfcmfmediamodule_media_display', [
                    'slug' => $medium->getSlug(),
                    'collectionSlug' => $medium->getCollection()->getSlug()
                ])
            ];
        }

        return $results;
    }

    public function getErrors()
    {
        return [];
    }
}
