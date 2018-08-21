<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Type;

use Cmfcmf\Module\MediaModule\Security\CollectionPermission\CollectionPermissionSecurityTree;
use Cmfcmf\Module\MediaModule\Security\SecurityGraph;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Fhaculty\Graph\Edge\Base;
use Fhaculty\Graph\Vertex;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Displays a permission level selector.
 */
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
        if ($options['permissionLevel'] == CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS) {
            $this->fixSecurityGraph();
        }
        $view->vars['securityGraph'] = $this->securityGraph;
        $view->vars['securityCategories'] = $this->securityManager->getCollectionSecurityCategories();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expanded' => true,
            'multiple' => true,
            'choices' => function (Options $options) {
                if ($options['permissionLevel'] == CollectionPermissionSecurityTree::PERM_LEVEL_ENHANCE_PERMISSIONS) {
                    $this->fixSecurityGraph();
                }

                return array_flip(array_map(function (Vertex $vertex) {
                    return $vertex->getAttribute('title');
                }, $this->securityGraph->getVertices()->getMap()));
            }
        ])->setRequired('permissionLevel');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_permission';
    }

    /**
     * Removes the change permissions vertex.
     */
    private function fixSecurityGraph()
    {
        if (!$this->securityGraph->hasVertex(
            CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS)
        ) {
            // Already fixed.
            return;
        }
        $changePermissionsVertex = $this->securityGraph->getVertex(
            CollectionPermissionSecurityTree::PERM_LEVEL_CHANGE_PERMISSIONS
        );
        /** @var Base $edge */
        foreach ($changePermissionsVertex->getEdges() as $edge) {
            $edge->destroy();
        }
        $this->securityGraph->removeVertex($changePermissionsVertex);
    }
}
