<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;

interface MediaTypeInterface
{
    public function getAlias();

    public function getEntityClass();

    public function getFormTypeClass();

    public function getDisplayName();

    public function toArray();

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound');

    public function renderFullpage(AbstractMediaEntity $entity);

    public function isEmbeddable();

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full');

    public function getExtendedMetaInformation(AbstractMediaEntity $entity);

    public function isEnabled();
}
