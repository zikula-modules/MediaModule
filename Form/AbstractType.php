<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form;

use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractType extends BaseAbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('version', HiddenType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $class = get_class($this);
        $class = substr($class, strlen('Cmfcmf\\Module\\MediaModule\\Form\\'));
        $class = substr($class, 0, -strlen('Type')) . 'Entity';

        $resolver->setDefaults([
            'data_class' => 'Cmfcmf\\Module\\MediaModule\\Entity\\' . $class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $class = get_class($this);
        $class = substr($class, strlen('Cmfcmf\\Module\\MediaModule\\Form\\'));
        $class = str_replace('\\', '_', $class);

        return 'cmfcmfmediamodule_' . strtolower($class);
    }
}
