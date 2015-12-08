<?php

namespace Cmfcmf\Module\MediaModule\Form;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\Common\I18n\TranslatableInterface;

class ImportType extends SymfonyAbstractType implements TranslatableInterface
{
    /**
     * @var FormTypeInterface
     */
    private $importerForm;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var
     */
    private $domain;

    public function __construct(FormTypeInterface $importerForm, TranslatorInterface $translator, $domain)
    {
        $this->importerForm = $importerForm;
        $this->translator = $translator;
        $this->domain = $domain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('collection', 'entity', [
                'required' => true,
                'class' => 'CmfcmfMediaModule:Collection\CollectionEntity',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('c');
                    $qb
                        ->orderBy('c.root', 'ASC')
                        ->addOrderBy('c.lft', 'ASC')
                        ->where($qb->expr()->not($qb->expr()->eq('c.id', ':uploadCollectionId')))
                        ->setParameter('uploadCollectionId', CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID);

                    return $qb;
                },
                'placeholder' => $this->__('Select collection'),
                'property' => 'indentedTitle',
            ])
            ->add('importSettings', $this->importerForm)
            ->add('import', 'submit', [
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
    }

    public function getName()
    {
        return 'cmfcmfmediamodule_settingstype';
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     *
     * @return string
     */
    public function __($msg)
    {
        return __($msg, $this->domain);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param int    $n  Count.
     *
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
        return _n($m1, $m2, $n, $this->domain);
    }

    /**
     * Format translations for modules.
     *
     * @param string       $msg   Message.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function __f($msg, $param)
    {
        return __f($msg, $param, $this->domain);
    }

    /**
     * Format pural translations for modules.
     *
     * @param string       $m1    Singular.
     * @param string       $m2    Plural.
     * @param int          $n     Count.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function _fn($m1, $m2, $n, $param)
    {
        return _fn($m1, $m2, $n, $param, $this->domain);
    }
}
