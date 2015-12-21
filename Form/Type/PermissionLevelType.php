<?php

namespace Cmfcmf\Module\MediaModule\Form\Type;

use Cmfcmf\Module\MediaModule\Security\SecurityGraph;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Fhaculty\Graph\Vertex;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermissionLevelType extends AbstractType
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var SecurityGraph
     */
    private $securityGraph;

    public function __construct(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
        $this->securityGraph = $securityManager->getCollectionSecurityGraph();
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['securityGraph'] = $this->securityGraph;
        $view->vars['securityCategories'] = $this->securityManager->getCollectionSecurityCategories();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => true,
            'multiple' => true,
            'choices' => array_map(function (Vertex $vertex) {
                return $vertex->getAttribute('title');
            }, $this->securityGraph->getVertices()->getMap())
        ]);
    }

    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_permission_level';
    }

    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }

    public function getName()
    {
        return get_class($this);
    }
}
