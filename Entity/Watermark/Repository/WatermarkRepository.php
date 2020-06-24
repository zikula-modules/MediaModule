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

namespace Cmfcmf\Module\MediaModule\Entity\Watermark\Repository;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Watermark\AbstractWatermarkEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class WatermarkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbstractWatermarkEntity::class);
    }

    /**
     * Removes all thumbnails generated for media with the given entity.
     *
     * @param AbstractWatermarkEntity $entity
     * @param CacheManager            $imagineCacheManager
     */
    public function cleanupThumbs(
        AbstractWatermarkEntity $entity,
        CacheManager $imagineCacheManager
    ) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('m')
            ->from(AbstractMediaEntity::class, 'm')
            ->leftJoin('m.collection', 'c')
            ->where($qb->expr()->eq('c.watermark', ':watermark'))
            ->setParameter('watermark', $entity);

        /** @var AbstractMediaEntity[] $media */
        $media = $qb->getQuery()->execute();
        foreach ($media as $medium) {
            $imagineCacheManager->remove($medium->getPath(), ['thumbnail', 'cmfcmfmediamodule.custom_image_filter']);
        }
    }
}
