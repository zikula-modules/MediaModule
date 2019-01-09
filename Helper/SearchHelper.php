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
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Composite;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Core\RouteUrl;
use Zikula\SearchModule\Entity\SearchResultEntity;
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

        include_once __DIR__ . '/../bootstrap.php';
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
            $result = new SearchResultEntity();
            $result->setTitle($medium->getTitle())
                ->setText($medium->getDescription())
                ->setModule('CmfcmfMediaModule')
                ->setCreated($medium->getCreatedDate())
                ->setSesid($sessionId)
                ->setUrl(new RouteUrl('cmfcmfmediamodule_media_display', [
                    'slug' => $medium->getSlug(),
                    'collectionSlug' => $medium->getCollection()->getSlug()
                ]))
            ;
            $results[] = $result;
        }

        return $results;
    }

    public function getErrors()
    {
        return [];
    }

    /**
     * Construct a QueryBuilder Where orX|andX Expr instance.
     *
     * @param QueryBuilder $qb
     * @param string[] $words  List of words to query for
     * @param string[] $fields List of fields to include into query
     * @param string $searchtype AND|OR|EXACT
     *
     * @return null|Composite
     */
    protected function formatWhere(QueryBuilder $qb, array $words = [], array $fields = [], $searchtype = 'AND')
    {
        if (empty($words) || empty($fields)) {
            return null;
        }

        $method = ('OR' == $searchtype) ? 'orX' : 'andX';
        /** @var $where Composite */
        $where = $qb->expr()->$method();
        $i = 1;
        foreach ($words as $word) {
            $subWhere = $qb->expr()->orX();
            foreach ($fields as $field) {
                $expr = $qb->expr()->like($field, "?$i");
                $subWhere->add($expr);
                $qb->setParameter($i, '%' . $word . '%');
                $i++;
            }
            $where->add($subWhere);
        }

        return $where;
    }
}
