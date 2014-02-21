<?php

namespace Bigfoot\Bundle\ImportBundle\Listener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Menu Listener
 */
class MenuListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $security;

    /**
     * @param SecurityContextInterface $security
     */
    public function __construct(SecurityContextInterface $security)
    {
        $this->security = $security;
    }

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
        $menu     = $event->getSubject();
        $fluxMenu = $menu->getChild('flux');

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $fluxMenu->addChild(
                'import',
                array(
                    'label'  => 'Imports',
                    'route'  => 'admin_datasource',
                    'extras' => array(
                        'routes' => array(
                            'admin_datasource_new',
                            'admin_datasource_edit'
                        )
                    ),
                    'linkAttributes' => array(
                        'icon' => 'level-down',
                    )
                )
            );
        }
    }
}
