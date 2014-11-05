<?php

namespace Bigfoot\Bundle\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DataMapperCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('bigfoot_import.data.mapper.factory')) {
            return;
        }

        $definition     = $container->getDefinition('bigfoot_import.data.mapper.factory');
        $taggedServices = $container->findTaggedServiceIds('bigfoot.import.mapper');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addMapper',
                array(new Reference($id), $id)
            );
        }
    }
}
