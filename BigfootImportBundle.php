<?php

namespace Bigfoot\Bundle\ImportBundle;

use Bigfoot\Bundle\ImportBundle\DependencyInjection\Compiler\DataMapperCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;


class BigfootImportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DataMapperCompilerPass());
    }
}
