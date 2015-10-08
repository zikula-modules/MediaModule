<?php

namespace Cmfcmf\Module\MediaModule\Form;

use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Zikula\Common\I18n\TranslatableInterface;

abstract class AbstractType extends BaseAbstractType implements TranslatableInterface
{
    protected $domain;

    public function __construct()
    {
        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('version', 'hidden');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $class = get_class($this);
        $class = substr($class, strlen('Cmfcmf\\Module\\MediaModule\\Form\\'));
        $class = substr($class, 0, -strlen('Type')) . 'Entity';

        $resolver->setDefaults([
            'data_class' => 'Cmfcmf\\Module\\MediaModule\\Entity\\' . $class
        ]);
    }

    public function getName()
    {
        $class = get_class($this);
        $class = substr($class, strlen('Cmfcmf\\Module\\MediaModule\\Form\\'));
        $class = str_replace('\\', '_', $class);

        return 'cmfcmfmediamodule_' . strtolower($class);
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
