<?php

namespace Cmfcmf\Module\MediaModule\Security;

class SecurityCategory
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
     * @var SecurityLevel[]
     */
    private $levels;

    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
        $this->levels = [];
    }

    /**
     * Add a new security level to this category.
     * @param SecurityLevel $level
     */
    public function addLevel(SecurityLevel $level)
    {
        $this->levels[] = $level;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return SecurityLevel[]
     */
    public function getLevels()
    {
        return $this->levels;
    }
}