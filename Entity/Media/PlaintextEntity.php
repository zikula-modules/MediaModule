<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PlaintextEntity extends AbstractFileEntity
{
    /**
     * @param string $dataDirectory
     */
    public function __construct($dataDirectory = '')
    {
        parent::__construct($dataDirectory);

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
