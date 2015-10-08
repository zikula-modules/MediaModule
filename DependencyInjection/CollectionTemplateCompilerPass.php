<?php

namespace Cmfcmf\Module\MediaModule\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class CollectionTemplateCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('cmfcmf_media_module.collection_template_collection')) {
            return;
        }

        $definition = $container->findDefinition('cmfcmf_media_module.collection_template_collection');
        $taggedServices = $container->findTaggedServiceIds('cmfcmf_media_module.collection_template');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addCollectionTemplate', [new Reference($id)]);
        }
    }
}
