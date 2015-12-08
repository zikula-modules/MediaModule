<?php

namespace Cmfcmf\Module\MediaModule\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ImporterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('cmfcmf_media_module.importer_collection')) {
            return;
        }

        $definition = $container->findDefinition('cmfcmf_media_module.importer_collection');
        $taggedServices = $container->findTaggedServiceIds('cmfcmf_media_module.importer');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addImporter', [new Reference($id)]);
        }
    }
}
