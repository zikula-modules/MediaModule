<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;

interface MediaTypeInterface
{
    public function getAlias();

    public function getEntityClass();

    public function getFormTypeClass();

    public function getFormOptions(AbstractMediaEntity $entity);

    public function getDisplayName();

    public function toArray();

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound');

    public function renderFullpage(AbstractMediaEntity $entity);

    public function isEmbeddable();

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full');

    public function getExtendedMetaInformation(AbstractMediaEntity $entity);

    public function isEnabled();
}
