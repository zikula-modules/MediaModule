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

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

/**
 * A category of collection permissions.
 */
class CollectionPermissionCategory
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @param string $id    The permission category id
     * @param string $title The permission title
     */
    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * Get the permission category id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the permission category title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
