<?php

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
use Doctrine\ORM\EntityRepository;

class WatermarkRepository extends EntityRepository
{
    /**
     * Removes all thumbnails generated for media with the given entity.
     *
     * @param AbstractWatermarkEntity       $entity
     * @param \SystemPlugin_Imagine_Manager $imagineManager
     */
    public function cleanupThumbs(
        AbstractWatermarkEntity $entity,
        \SystemPlugin_Imagine_Manager $imagineManager
    ) {
        $imagineManager->setModule('CmfcmfMediaModule');

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('m')
            ->from('CmfcmfMediaModule:Media\AbstractMediaEntity', 'm')
            ->leftJoin('m.collection', 'c')
            ->where($qb->expr()->eq('c.watermark', ':watermark'))
            ->setParameter('watermark', $entity);

        /** @var AbstractMediaEntity[] $media */
        $media = $qb->getQuery()->execute();
        foreach ($media as $medium) {
            $imagineManager->removeObjectThumbs($medium->getImagineId());
        }
    }
}
