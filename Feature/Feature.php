<?php

namespace Cmfcmf\Module\MediaModule\Feature;

class Feature
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @param string $id
     * @param string|null $description
     */
    public function __construct($id, $description = null)
    {
        $this->id = $id;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}