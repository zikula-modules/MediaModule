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
     * SecurityLevel constructor.
     * @param $id
     * @param $title
     * @param $description
     * @param $category
     * @param $requiredSecurityLevels
     */
    public function __construct($id, $title, $description, $category, $requiredSecurityLevels = [])
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->requiredSecurityLevels = $requiredSecurityLevels;
    }
}