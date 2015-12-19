<?php

namespace Cmfcmf\Module\MediaModule\Form\Type;

use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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
        $view->vars['securityTree'] = $this->securityManager->getCollectionSecurityTree();
    }

    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_permission_level';
    }

    public function getParent()
    {
        return FormType::class;
    }
}