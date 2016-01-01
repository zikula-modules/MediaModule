<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FontCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('cmfcmf_media_module.font_collection')) {
            return;
        }

        $definition = $container->findDefinition('cmfcmf_media_module.font_collection');
        $taggedServices = $container->findTaggedServiceIds('cmfcmf_media_module.font');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addFontLoader', [new Reference($id)]);
        }
    }
}
