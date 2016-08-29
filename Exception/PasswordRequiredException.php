<?php


namespace Cmfcmf\Module\MediaModule\Exception;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PasswordRequiredException extends AccessDeniedException
{
    /**
     * @var CollectionEntity
     */
    private $collection;

    /**
     * @var string
     */
    private $permissionLevel;

    /**
     * @param CollectionEntity $collection
     * @param string           $permissionLevel
     */
    public function __construct(CollectionEntity $collection, $permissionLevel)
    {
        parent::__construct('Password Required.');
        $this->collection = $collection;
        $this->permissionLevel = $permissionLevel;
    }

    /**
     * @return CollectionEntity
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return int
     */
    public function getPermissionLevel()
    {
        return $this->permissionLevel;
    }
}
