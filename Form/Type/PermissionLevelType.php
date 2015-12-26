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
        // @todo Add an option to disallow selecting the change permission level.
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

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'cmfcmfmediamodule_permission';
    }
}
