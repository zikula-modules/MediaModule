<?php

namespace Cmfcmf\Module\MediaModule\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CollectionPermissionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('cmfcmf_media_module.collection_template_collection')) {
            return;
        }

        $definition = $container->findDefinition('cmfcmf_media_module.collection_permission.container');
        $taggedServices = $container->findTaggedServiceIds('cmfcmf_media_module.collection_permission');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addCollectionPermission', [new Reference($id)]);
        }
    }
}
