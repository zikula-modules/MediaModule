<?php

namespace Cmfcmf\Module\MediaModule\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class MediaTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('cmfcmf_media_module.media_type_collection')) {
            return;
        }

        $definition = $container->findDefinition('cmfcmf_media_module.media_type_collection');
        $taggedServices = $container->findTaggedServiceIds('cmfcmf_media_module.media_type');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addMediaType', [new Reference($id)]);
        }
    }
}
