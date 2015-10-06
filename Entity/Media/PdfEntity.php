<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PdfEntity extends AbstractFileEntity
{
    public function onNewFile(array $info)
    {
        parent::onNewFile($info);

        // @todo read meta tags.
    }
}
