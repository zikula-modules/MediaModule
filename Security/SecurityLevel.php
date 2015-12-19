<?php

namespace Cmfcmf\Module\MediaModule\Security;

class SecurityLevel
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
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $category;

    /**
     * @var SecurityLevel[]
     */
    private $requiredLevels;

    /**
     * @var SecurityLevel[]
     */
    private $disallowedLevels;

    /**
     * SecurityLevel constructor.
     *
     * @param int $id
     * @param string $title
     * @param string $description
     * @param string $category
     * @param SecurityLevel[] $requiredSecurityLevels
     * @param SecurityLevel[] $disallowedSecurityLevels
     */
    public function __construct($id, $title, $description, $category, $requiredSecurityLevels, $disallowedSecurityLevels)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->requiredLevels = $requiredSecurityLevels;
        $this->disallowedLevels = $disallowedSecurityLevels;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return null|string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return SecurityLevel[]
     */
    public function getRequiredLevels()
    {
        return $this->requiredLevels;
    }

    /**
     * @return SecurityLevel[]
     */
    public function getDisallowedLevels()
    {
        return $this->disallowedLevels;
    }

    /**
     * Returns the ids of all required security levels.
     *
     * @return int[]
     */
    public function getRequiredLevelIds()
    {
        return array_map(function (SecurityLevel $level) {
            return $level->getId();
        }, $this->requiredLevels);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the ids of all disallowed security levels.
     *
     * @return int[]
     */
    public function getDisallowedLevelIds()
    {
        return array_map(function (SecurityLevel $level) {
            return $level->getId();
        }, $this->disallowedLevels);
    }
}
