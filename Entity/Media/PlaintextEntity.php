<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PlaintextEntity extends AbstractFileEntity
{
    public function __construct()
    {
        parent::__construct();

        $this->setUseSyntaxHighlighting(true);
    }

    public function getUseSyntaxHighlighting()
    {
        return isset($this->extraData['useSyntaxHighlighting']) ? $this->extraData['useSyntaxHighlighting'] : false;
    }

    public function setUseSyntaxHighlighting($useSyntaxHighlighting)
    {
        $this->extraData['useSyntaxHighlighting'] = $useSyntaxHighlighting;
    }
}
