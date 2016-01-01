<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule;

use Cmfcmf\Module\MediaModule\DependencyInjection\CollectionPermissionCompilerPass;
use Cmfcmf\Module\MediaModule\DependencyInjection\CollectionTemplateCompilerPass;
use Cmfcmf\Module\MediaModule\DependencyInjection\FontCompilerPass;
use Cmfcmf\Module\MediaModule\DependencyInjection\MediaTypeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Core\AbstractModule;

class CmfcmfMediaModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     *
     * Adds compiler passes to the container.
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MediaTypeCompilerPass());
        $container->addCompilerPass(new CollectionTemplateCompilerPass());
        $container->addCompilerPass(new FontCompilerPass());
        $container->addCompilerPass(new CollectionPermissionCompilerPass());
    }
}
