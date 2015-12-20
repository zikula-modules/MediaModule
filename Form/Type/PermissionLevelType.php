<?php

namespace Cmfcmf\Module\MediaModule\Form\Type;

use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class PermissionLevelType extends AbstractType
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    public function __construct(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['securityGraph'] = $this->securityManager->getCollectionSecurityGraph();
        $view->vars['securityCategories'] = $this->securityManager->getCollectionSecurityCategories();
    }

    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_permission_level';
    }

    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }

    public function getName()
    {
        return get_class($this);
    }
}