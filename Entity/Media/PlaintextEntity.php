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

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @ORM\Entity()
 */
class PlaintextEntity extends AbstractFileEntity
{
    /**
     * @param RequestStack $requestStack
     * @param string       $dataDirectory
     */
    public function __construct(RequestStack $requestStack, $dataDirectory = '')
    {
        parent::__construct($requestStack, $dataDirectory);

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
