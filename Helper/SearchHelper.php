<?php

namespace Cmfcmf\Module\MediaModule\Helper;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Zikula\Core\RouteUrl;
use Zikula\SearchModule\AbstractSearchable;

class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param boolean $active if the module should be checked as active
     * @param array|null $modVars module form vars as previously set
     * @return string
     */
    public function getOptions($active, $modVars = null)
    {
        return $this->getContainer()->get('templating')
            ->render('@CmfcmfMediaModule/Search/options.html.twig', ['active' => $active]);
    }

    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
     * @return array
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        $results = [];
        $securityManager = $this->getContainer()->get('cmfcmf_media_module.security_manager');
        $sessionId = $this->container->get('session')->getId();

        $qb = $securityManager->getCollectionsWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW);
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

        $qb = $securityManager->getMediaWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_MEDIA_DETAILS);
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
}