<?php

namespace Cmfcmf\Module\MediaModule\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\CategoriesModule\Entity\AbstractCategoryAssignment;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cmfcmfmedia_category_media")
 */
class MediaCategoryAssignmentEntity extends AbstractCategoryAssignment
{
    /**
     * @ORM\ManyToOne(targetEntity="Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity", inversedBy="categoryAssignments")
     * @var AbstractMediaEntity
     */
    private $entity;

    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
