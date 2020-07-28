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

namespace Cmfcmf\Module\MediaModule\Helper;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\RouteUrl;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;

class SearchHelper implements SearchableInterface
{
    public function getBundleName(): string
    {
        return 'CmfcmfMediaModule';
    }

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SecurityManager
     */
    private $securityManager;

    public function __construct(
        RequestStack $requestStack,
        SecurityManager $securityManager
    ) {
        $this->requestStack = $requestStack;
        $this->securityManager = $securityManager;

        include_once __DIR__ . '/../bootstrap.php';
    }

    public function amendForm(FormBuilderInterface $builder): void
    {
        // nothing
    }

    public function getResults(array $words, $searchType = 'AND', ?array $modVars = []): array
    {
        $results = [];
        $sessionId = $this->requestStack->getCurrentRequest()->getSession()->getId();

        $qb = $this->securityManager->getCollectionsWithAccessQueryBuilder(CollectionPermissionSecurityTree::PERM_LEVEL_OVERVIEW);
        $where = $this->formatWhere($qb, $words, ['c.title'], $searchType);
        $qb->andWhere($where);
        /** @var CollectionEntity[] $collections */
        $collections = $qb->getQuery()->execute();

        foreach ($collections as $collection) {
            $result = new SearchResultEntity();
            $result->setTitle($collection->getTitle())
                ->setText($collection->getDescription())
                ->setModule('CmfcmfMediaModule')
//                ->setCreated($collection->getCreatedDate())
                ->setSesid($sessionId)
                ->setUrl(new RouteUrl('cmfcmfmediamodule_collection_display', ['slug' => $collection->getSlug()]))
            ;
            $results[] = $result;
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
//                ->setCreated($medium->getCreatedDate())
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

    public function getErrors(): array
    {
        return [];
    }

    /**
     * Construct a QueryBuilder Where orX|andX Expr instance.
     *
     * @param string[] $words  List of words to query for
     * @param string[] $fields List of fields to include into query
     * @param string $searchtype AND|OR|EXACT
     *
     * @return Composite|null
     */
    protected function formatWhere(QueryBuilder $qb, array $words = [], array $fields = [], $searchtype = 'AND')
    {
        if (empty($words) || empty($fields)) {
            return null;
        }

        $method = ('OR' === $searchtype) ? 'orX' : 'andX';
        /** @var $where Composite */
        $where = $qb->expr()->{$method}();
        $i = 1;
        foreach ($words as $word) {
            $subWhere = $qb->expr()->orX();
            foreach ($fields as $field) {
                $expr = $qb->expr()->like($field, "?${i}");
                $subWhere->add($expr);
                $qb->setParameter($i, '%' . $word . '%');
                $i++;
            }
            $where->add($subWhere);
        }

        return $where;
    }
}
