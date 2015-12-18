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
    private $requiredSecurityLevels;

    /**
     * @var SecurityLevel[]
     */
    private $disallowedSecurityLevels;

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
        $this->requiredSecurityLevels = $requiredSecurityLevels;
        $this->disallowedSecurityLevels = $disallowedSecurityLevels;
    }
}
