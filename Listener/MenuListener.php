<?php

namespace Bigfoot\Bundle\ImportBundle\Listener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

/**
 * Menu Listener
 */
class MenuListener implements EventSubscriberInterface
{
    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            MenuEvent::GENERATE_MAIN => 'onGenerateMain',
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function onGenerateMain(GenericEvent $event)
    {
        $menu      = $event->getSubject();
        $fluxMenu = $menu->getChild('flux');

        $fluxMenu->addChild(
            'import',
            array(
                'label'  => 'Imports',
                'route'  => 'admin_datasource',
                'linkAttributes' => array(
                    'icon' => 'level-down',
                )
            )
        );
    }
}
