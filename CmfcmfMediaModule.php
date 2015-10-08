<?php

namespace Cmfcmf\Module\MediaModule;

use Cmfcmf\Module\MediaModule\DependencyInjection\CollectionTemplateCompilerPass;
use Cmfcmf\Module\MediaModule\DependencyInjection\FontCompilerPass;
use Cmfcmf\Module\MediaModule\DependencyInjection\MediaTypeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Core\AbstractModule;

class CmfcmfMediaModule extends AbstractModule
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MediaTypeCompilerPass());
        $container->addCompilerPass(new CollectionTemplateCompilerPass());
        $container->addCompilerPass(new FontCompilerPass());
    }
}
