<?php

namespace Cmfcmf\Module\MediaModule\Security\CollectionPermission;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractCollectionPermission implements CollectionPermissionInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getId()
    {
        return strtolower($this->getType());
    }

    public function getFormClass()
    {
        return 'Cmfcmf\Module\MediaModule\Form\Collection\Permission\\' . $this->getType() . 'PermissionType';
    }

    protected function getType()
    {
        $class = get_class($this);

        return substr($class, strrpos($class, '\\') + 1, -strlen('CollectionPermission'));
    }

    public function getEntityClass()
    {
        return 'Cmfcmf\Module\MediaModule\Entity\Collection\Permission\\' . $this->getType() . 'PermissionEntity';
    }

    /**
     * @param QueryBuilder $qb
     * @param              $entity
     * @param              $type
     * @param              $value
     * @param              $field
     *
     * @return \Doctrine\ORM\Query\Expr\Composite
     */
    public static function whereInSimpleArray(QueryBuilder &$qb, $entity, $type, $value, $field)
    {
        $qb->setParameter($type . '1', $value);
        $qb->setParameter($type . '2', "%," . $value);
        $qb->setParameter($type . '3', $value . ",%");
        $qb->setParameter($type . '4', "%," . $value . ",%");

        return $qb->expr()->orX(
            $qb->expr()->eq("$entity.$field", ':' . $type . '1'),
            $qb->expr()->like("$entity.$field", ':' . $type . '2'),
            $qb->expr()->like("$entity.$field", ':' . $type . '3'),
            $qb->expr()->like("$entity.$field", ':' . $type . '4')
        );
    }
}
